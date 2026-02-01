# Apivalk

[![Website](https://img.shields.io/badge/website-apivalk.com-blue)](https://apivalk.com)
[![Documentation](https://img.shields.io/badge/docs-docs.apivalk.com-green)](https://docs.apivalk.com)

### Apivalk - The OpenAPI-First Framework for REST APIs That Soar

A Lightweight, Framework-Agnostic REST API Ecosystem for PHP. Built for speed, precision, and type-safe development.

Harness the power of the Valkyrie. APIs that soar instead of crawl. Apivalk gives you an OpenAPI-first mind that brings
structure, automation, and clarity to your backend.

⚡ **OpenAPI-first** 🔒 **Scope-based Security** 🧠 **Type-safe** 🪶 **Lightweight** 🔮 **Framework-agnostic**

---

## 🔒 Security & Authorization

Apivalk features a robust, OpenAPI-compliant security system out of the box.

- **Identity System**: Unified handling of `UserAuthIdentity` and `GuestAuthIdentity`.
- **Scope Objects**: Granular, type-safe authorization using `Scope` objects instead of simple strings.
- **JWT & OAuth**: First-class support for JWK-based JWT validation via `JwtAuthenticator` (based on
  `firebase/php-jwt`).
- **Middleware Pipeline**: Dedicated `AuthenticationMiddleware` and `SecurityMiddleware` for clean, decoupled
  authorization.

---

## Why Apivalk?

- **OpenAPI-First**: One definition powering models, validation, routing, and documentation.
- **REST APIs, Done Right**: A clean, modern approach with structure and best practices built in.
- **Unified Standards**: Eliminating fragmentation in PHP API development.
- **Framework-Agnostic**: Use it with Laravel, Symfony, Slim, or native PHP. No lock-in.

---

## Key Capabilities

### 🚀 Fast Routing Engine

Ultra-light, minimal, and optimized routing layer inspired by PSR-7/15 conventions, with support for route caching.

### 🔒 Type-Safe Architecture

Strong typing for all requests and responses, strict DTOs, and predictable interfaces, even on PHP 7.2.

### 🔄 Middleware System

A modern middleware pipeline for Auth, Rate Limiting, CORS, and more.

### 📝 API Documentation

Automatic OpenAPI documentation generation from PHP code annotations with Swagger UI support.

---

## Framework Bridges

While Apivalk is fully framework-agnostic, we offer dedicated integration bridges:

- **[Apivalk Laravel Bridge](https://github.com/apivalk/laravel-bridge)**: Automatic bootstrapping, uses Laravel's
  Request/Response, and drop-in `{any}` fallback route.
- **[Apivalk Symfony Bridge](https://github.com/apivalk/symfony-bridge)**: Registered as a HttpKernel controller,
  integrates with Symfony routing and DI container.

---

## Getting Started

### Installation

```bash
composer require apivalk/apivalk
```

### Typical Structure

```text
app/Http/Api/Controller/Pet/CreatePetController.php
app/Http/Api/Request/Pet/CreatePetRequest.php
app/Http/Api/Response/Pet/CreatePetResponse.php
```

### Bootstrapping

```php
use apivalk\apivalk\Apivalk;
use apivalk\apivalk\ApivalkConfiguration;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use Psr\Container\ContainerInterface;

$apivalkConfiguration = new ApivalkConfiguration(
    $router, // AbstractRouter instance
    $renderer, // Optional: RendererInterface
    $exceptionHandler, // Optional: callable
    $container // Optional: ContainerInterface
);

$apivalk = new Apivalk($apivalkConfiguration);
$response = $apivalk->run();

// $response is an AbstractApivalkResponse instance.
// If you want to output the response using the configured renderer:
$apivalk->getRenderer()->render($response);
```

---

## Apivalk Ecosystem

- **[Apivalk](https://apivalk.com)**: OpenAPI-first REST API framework for PHP.
- **Apivalk CLI** (Planned): Scaffolding and generators for local DX.
- **Apivalk Cloud** (In Research): Managed platform for Apivalk APIs.

Official Website: [apivalk.com](https://apivalk.com) | Documentation: [docs.apivalk.com](https://docs.apivalk.com)

---

## Contributing and local development

- For local development, fork this repository.
- After checking out your branch, build the images:
    - `docker compose build`

  If you prefer, you can also use your own PHP setup or tools like DDEV, Lando, etc. In that case, the docker steps
  below are optional, and on your responsibility.

- Run commands inside the PHP 7.2 container like this:
    - `docker compose run --rm php72 <command>`

  Examples:
    - `docker compose run --rm php72 php -v`
    - `docker compose run --rm php72 composer install`
    - `docker compose run --rm php72 composer test`
    - `docker compose run --rm php72 composer phpstan`

---

## Sponsors ❤️

We love our Sponsors! (Platinum, Gold, and Bronze sponsors list)

---

© 2025 Apivalk. All rights reserved.
Main maintainer and founder: **Dominic Poppe**.
