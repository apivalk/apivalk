<p align="center">
  <img src="docs/img/logo.png" alt="Apivalk" width="200" />
</p>

# Apivalk 🦅

[![Packagist Version](https://img.shields.io/packagist/v/apivalk/apivalk)](https://packagist.org/packages/apivalk/apivalk)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-blue)](https://www.php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Docs](https://img.shields.io/badge/docs-docs.apivalk.com-6366f1)](https://docs.apivalk.com)

**OpenAPI-first PHP framework for type-safe REST APIs.** Framework-agnostic · PSR-7/15/11/3 · PHP 7.2+

---

## The Problem

OpenAPI specs drift from code. `$_POST['name']` has no type. Nobody knows which endpoints are secured. You maintain two things — the code and the docs — and they never quite agree.

Apivalk makes your PHP classes the single source of truth. Define a property once → automatic validation, type casting, OpenAPI 3.0 generation, and full IDE autocompletion.

---

## Why Apivalk? 🤔

- 📄 **Code is the spec** — `getDocumentation()` on your request/response classes drives validation, type casting, and OpenAPI generation from one definition. No annotation parsing, no separate YAML.
- 🔍 **Zero route registration** — drop a controller into your directory. `ClassLocator` auto-discovers it and caches the route index. No config files to update.
- ⚡ **Resource CRUD** — one `AbstractResource` declaration generates five typed CRUD endpoints with full OpenAPI coverage. ~15 hand-authored classes collapse into one resource + five thin controllers.
- 🔒 **Security built in** — JWT (JWK-based), scope enforcement, and three route security levels out of the box.
- 🧠 **Typed everything** — by the time `__invoke()` runs, input is sanitized, validated, and cast. You get `$request->body()->name`, not `$_POST['name']`.
- 💡 **Full IDE autocompletion** — `DocBlockGenerator` rewrites your request classes with typed `@method` annotations and generates `Shape/` classes per bag. `$request->body()->`, `$request->sorting()->`, `$request->filtering()->` all autocomplete with correct types in PhpStorm and VS Code — zero hand-written boilerplate.

---

## Installation

```bash
composer require apivalk/apivalk
```

> PHP 7.2+, `ext-json`, `ext-mbstring` — [full installation guide →](https://docs.apivalk.com/installation)

---

## Quick Start

### Bootstrap

```php
<?php
declare(strict_types=1);

use apivalk\apivalk\Apivalk;
use apivalk\apivalk\ApivalkConfiguration;
use apivalk\apivalk\ApivalkExceptionHandler;
use apivalk\apivalk\Cache\FilesystemCache;
use apivalk\apivalk\Middleware\RequestValidationMiddleware;
use apivalk\apivalk\Middleware\SanitizeMiddleware;
use apivalk\apivalk\Router\Router;
use apivalk\apivalk\Util\ClassLocator;

require __DIR__ . '/vendor/autoload.php';

$classLocator = new ClassLocator(__DIR__ . '/src/Http/Controller', 'App\\Http\\Controller');
$router = new Router($classLocator, new FilesystemCache(__DIR__ . '/var/cache'));

$configuration = new ApivalkConfiguration(
    $router,
    null,                                        // default: JsonRenderer
    [ApivalkExceptionHandler::class, 'handle']
);

$configuration->getMiddlewareStack()->add(new SanitizeMiddleware());
$configuration->getMiddlewareStack()->add(new RequestValidationMiddleware());

$apivalk = new Apivalk($configuration);
$response = $apivalk->run();
$apivalk->getRenderer()->render($response);
```

> Every controller in `src/Http/Controller` is auto-discovered on first boot and cached. No routes to register. → [Configure Apivalk](https://docs.apivalk.com/how-to/configure)

### Define an Endpoint

Every endpoint is a **Controller + Request + Response** triplet. The Request defines the shape — it drives validation and OpenAPI. The Response defines the output schema.

```php
// Controller — owns the route and the business logic
final class ReadPetController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::get('/v1/pets/{id}')->description('Get a pet by ID');
    }

    public static function getRequestClass(): string  { return ReadPetRequest::class; }
    public static function getResponseClasses(): array { return [ReadPetResponse::class, NotFoundApivalkResponse::class]; }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $pet = $this->petRepo->find($request->path()->id); // id is cast to int automatically
        return $pet ? new ReadPetResponse($pet) : new NotFoundApivalkResponse('Pet not found');
    }
}

// Request — declares the input shape; drives validation + OpenAPI
class ReadPetRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        $doc = new ApivalkRequestDocumentation();
        $doc->addPathProperty(new IntegerProperty('id', 'Pet ID'));
        return $doc;
    }
}

// Response — declares the output shape; drives OpenAPI schema
class ReadPetResponse extends AbstractApivalkResponse
{
    public static function getStatusCode(): int { return self::HTTP_200_OK; }

    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        $doc = new ApivalkResponseDocumentation();
        $doc->addProperty(new IntegerProperty('id', 'Pet ID'));
        $doc->addProperty(new StringProperty('name', 'Pet name'));
        return $doc;
    }

    public function toArray(): array { return ['id' => $this->pet['id'], 'name' => $this->pet['name']]; }
}
```

`RequestValidationMiddleware` returns `422` with field-level errors automatically. → [Controllers](https://docs.apivalk.com/http/controller) · [Requests](https://docs.apivalk.com/http/request) · [Responses](https://docs.apivalk.com/http/response)

---

## Features

### 🛣️ Routing
Auto-discovery, filesystem route caching, fluent builder (`Route::get/post/put/patch/delete`), automatic 404/405 handling, path parameters via `{name}` syntax. → [Routing docs](https://docs.apivalk.com/routing/route)

### 💡 IDE Autocompletion via DocBlock Generator

Run `DocBlockGenerator` once (or as a CI step) and your request classes get full IDE support — no hand-written boilerplate.

**Before:**
```php
class ReadPetRequest extends AbstractApivalkRequest { /* empty */ }
```

**After** (auto-generated):
```php
/**
 * @method ParameterBag|Shape\ReadPetPathShape path()
 * @method ParameterBag|Shape\ReadPetBodyShape body()
 * @method SortBag|Shape\ReadPetSortingShape sorting()
 * @method FilterBag|Shape\ReadPetFilteringShape filtering()
 * @method Paginator|null paginator()
 */
class ReadPetRequest extends AbstractApivalkRequest { /* still empty */ }
```

`$request->path()->id`, `$request->body()->name`, `$request->sorting()->createdAt` — all autocomplete with their correct types. Works for resource controllers too: `DocBlockGenerator` emits `@property` annotations on `AbstractResource` subclasses and generates typed list request classes (`AnimalListRequest`) with fully wired sort/filter/paginator shapes.

```php
$generator = new DocBlockGenerator();
$generator->run('/src/Http/Controller', 'App\\Http\\Controller');
```

→ [DocBlock generator docs](https://docs.apivalk.com/documentation/docblock-generator) · [Generate how-to](https://docs.apivalk.com/how-to/generate-openapi)

### 🧅 Middleware Pipeline
Onion-style PSR-15 pipeline. Built in: `SanitizeMiddleware`, `RequestValidationMiddleware`, `AuthenticationMiddleware`, `SecurityMiddleware`, `RateLimitMiddleware`. Trivial to extend with your own. → [Middleware docs](https://docs.apivalk.com/middleware/index) · [Custom middleware](https://docs.apivalk.com/how-to/custom-middleware)

### 🔒 Security & Authorization
Three route security levels — public, authenticated-only, scoped. `JwtAuthenticator` supports JWK endpoints out of the box. Missing scope → `403 Forbidden`. No token → `401 Unauthorized`. Custom authenticators supported via `AuthenticatorInterface`. → [Security docs](https://docs.apivalk.com/security/index) · [JWT how-to](https://docs.apivalk.com/how-to/jwt-auth) · [API key how-to](https://docs.apivalk.com/how-to/api-key-auth)

### 📄 OpenAPI 3.0 Generation
`OpenAPIGenerator` introspects every controller's request and response classes and emits a complete OpenAPI 3.0 spec — including pagination envelopes, `X-RateLimit-*` headers, locale headers, and per-operation security requirements. No annotations. Run it as a `bin/` script and drop the JSON behind Swagger UI. → [OpenAPI generator](https://docs.apivalk.com/documentation/openapi-generator) · [Generate how-to](https://docs.apivalk.com/how-to/generate-openapi)

### 📦 Resource CRUD
Declare an `AbstractResource` once — identifier, properties, filters, sortings — and get five fully typed, validated, OpenAPI-documented CRUD endpoints with matching response envelopes. Only `__invoke()` is yours to write. → [Resources](https://docs.apivalk.com/resources/index) · [Resource CRUD how-to](https://docs.apivalk.com/how-to/resource-crud)

### 📃 Pagination
Three strategies per route: `Pagination::page()`, `Pagination::offset()`, `Pagination::cursor()`. Apivalk handles query param validation, paginator hydration, and JSON envelope (`data` + `pagination`). All shapes documented in OpenAPI automatically. → [Pagination docs](https://docs.apivalk.com/http/pagination) · [Pagination how-to](https://docs.apivalk.com/how-to/pagination)

### 🔢 Sorting & Filtering
Declare allowed sort fields and filter types on the route. Sorting defaults are applied when `order_by` is omitted — `$request->sorting()` is always populated. Undeclared filter keys are silently ignored. → [Sorting](https://docs.apivalk.com/http/sorting) · [Filtering](https://docs.apivalk.com/http/filtering)

### ⏱️ Rate Limiting
Per-route IP-based rate limiting. `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset` on every response; `Retry-After` on `429`. Documented in OpenAPI automatically. → [Rate limiting](https://docs.apivalk.com/middleware/rate-limit)

### 🌍 Localization
Locale resolved from `Accept-Language` on every request, `Content-Language` set on every response. Both headers documented in OpenAPI. Zero boilerplate in controllers. → [Localization docs](https://docs.apivalk.com/http/localization)

### ⚙️ Dependency Injection
Pass any PSR-11 container — PHP-DI, Symfony DI, or your own. Apivalk uses it to resolve controllers, enabling full constructor injection. Without a container it falls back to `new ControllerClass()`. → [Configuration](https://docs.apivalk.com/configuration)

---

## Built-in Error Responses

`BadRequestApivalkResponse` (400) · `UnauthorizedApivalkResponse` (401) · `ForbiddenApivalkResponse` (403) · `NotFoundApivalkResponse` (404) · `MethodNotAllowedApivalkResponse` (405) · `BadValidationApivalkResponse` (422) · `TooManyRequestsApivalkResponse` (429) · `InternalServerErrorApivalkResponse` (500)

---

## Contributing & Local Development

```bash
docker compose build
docker compose run --rm php72 composer install
docker compose run --rm php72 composer test      # PHPUnit
docker compose run --rm php72 composer phpstan   # PHPStan level 6
```

Own PHP 7.2+ setup? Docker is optional — DDEV, Lando, or native all work. PHPStan runs at level 6; new code must not add violations (a baseline covers pre-existing issues). → [Contributing guide](https://docs.apivalk.com/contributing)

---

📚 **[docs.apivalk.com](https://docs.apivalk.com)** · 🌐 **[apivalk.com](https://apivalk.com)** · 🐛 **[Issues](https://github.com/apivalk/apivalk/issues)**

© 2025 Apivalk. MIT License. Maintainer: **Dominic Poppe**.
