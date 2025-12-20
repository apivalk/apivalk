# Apivalk

[![Website](https://img.shields.io/badge/website-apivalk.com-blue)](https://apivalk.com)
[![Documentation](https://img.shields.io/badge/docs-docs.apivalk.com-green)](https://docs.apivalk.com)

### Apivalk - The OpenAPI-First Framework for REST APIs That Soar

A Lightweight, Framework-Agnostic REST API Ecosystem for PHP. Built for speed, precision, and type-safe development.

Harness the power of the Valkyrie. APIs that soar instead of crawl. Apivalk gives you an OpenAPI-first mind that brings
structure, automation, and clarity to your backend.

⚡ **OpenAPI-first** 🧠 **Type-safe** 🪶 **Lightweight** 🔮 **Framework-agnostic**

---

## Why Apivalk?

- **OpenAPI-First**: One definition powering models, validation, routing, and documentation.
- **REST APIs, Done Right**: A clean, modern approach with structure and best practices built in.
- **Unified Standards**: Eliminating fragmentation in PHP API development.
- **Framework-Agnostic**: Use it with Laravel, Symfony, Slim, or native PHP. No lock-in.

---

## Key Capabilities

### 🚀 Fast Routing Engine

Ultra-light, minimal and optimized routing layer with support for PSR-7/15 conventions and route caching.

### 🔒 Type-Safe Architecture

Strong typing for all requests and responses, strict DTOs, and predictable interfaces, even on PHP 7.2.

### 🔄 Middleware System

A modern PSR-15 compatible middleware pipeline for Auth, Rate Limiting, CORS, and more.

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
composer require apivalk/apivalk-php
```

### Typical Structure

```text
app/Http/Api/Controller/Pet/CreatePetController.php
app/Http/Api/Request/Pet/CreatePetRequest.php
app/Http/Api/Response/Pet/CreatePetResponse.php
```

### Bootstrapping

```php
$apivalkConfiguration = new ApivalkConfiguration(
    $router, // AbstractRouter instance
    $renderer, // Optional: apivalk\apivalk\Http\Renderer\RendererInterface
    $exceptionHandler, // Optional: callable
    $container // Optional: Psr\Container\ContainerInterface
);

$apivalk = new Apivalk($apivalkConfiguration);
$response = $apivalk->run($request);

// $response is a PSR ResponseInterface instance.
// do whatever you want with it
// if you want to fully use Apivalk you can use the renderer:
// $apivalk->getRenderer()->render($response);
```

---

## Apivalk Ecosystem

- **[Apivalk](https://apivalk.com)**: OpenAPI-first REST API framework for PHP.
- **Apivalk CLI** (Planned): Scaffolding and generators for local DX.
- **Apivalk Cloud** (In Research): Managed platform for Apivalk APIs.

Official Website: [apivalk.com](https://apivalk.com) | Documentation: [docs.apivalk.com](https://docs.apivalk.com)

---

## Sponsors ❤️

We love our Sponsors! (Platinum, Gold, and Bronze sponsors list)

---

© 2025 Apivalk. All rights reserved.
Main maintainer and founder: **Dominic Poppe**.
