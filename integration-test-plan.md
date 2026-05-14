# Integration Test Plan: Real-World API

## Goal

Build a fully integrated, real-world-style test suite that exercises the complete Apivalk request lifecycle:

```
Superglobals → Router → MiddlewareStack → Controller (or early return) → Response
```

All controllers, resources, request/response classes, and test infrastructure live in `Tests/Integration/RealWorld/`. PHPUnit test classes live in `Tests/Integration/RealWorld/Tests/`. Nothing is mocked except the authenticator and cache — the middleware stack, router, request population, and validation all run for real.

---

## Endpoints

| Method | Path | Controller type | Scope | Permission |
|--------|------|-----------------|-------|------------|
| GET | `/v1/api/customers` | non-resource | `api:customer` | `api:customer:read` |
| POST | `/v1/api/customers` | non-resource | `api:customer` | `api:customer:create` |
| GET | `/v1/api/customers/{customer_id}` | non-resource | `api:customer` | `api:customer:read` |
| PATCH | `/v1/api/customers/{customer_id}` | non-resource | `api:customer` | `api:customer:update` |
| DELETE | `/v1/api/customers/{customer_id}` | non-resource | `api:customer` | `api:customer:delete` |
| GET | `/v1/api/customers/{customer_id}/addresses` | non-resource | `api:customer:address` | `api:customer:address:read` |
| POST | `/v1/api/customers/{customer_id}/addresses` | non-resource | `api:customer:address` | `api:customer:address:create` |
| GET | `/v1/api/customers/{customer_id}/addresses/{address_uuid}` | non-resource | `api:customer:address` | `api:customer:address:read` |
| PATCH | `/v1/api/customers/{customer_id}/addresses/{address_uuid}` | non-resource | `api:customer:address` | `api:customer:address:update` |
| DELETE | `/v1/api/customers/{customer_id}/addresses/{address_uuid}` | non-resource | `api:customer:address` | `api:customer:address:delete` |
| GET | `/v1/api/contracts` | resource | `api:contract` | `api:contract:read` |
| POST | `/v1/api/contracts` | resource | `api:contract` | `api:contract:create` |
| GET | `/v1/api/contracts/{contract_uuid}` | resource | `api:contract` | `api:contract:read` |
| PATCH | `/v1/api/contracts/{contract_uuid}` | resource | `api:contract` | `api:contract:update` |
| DELETE | `/v1/api/contracts/{contract_uuid}` | resource | `api:contract` | `api:contract:delete` |
| GET | `/v1/api/contracts/{contract_uuid}/invoices` | resource | `api:contract:invoice` | `api:contract:invoice:read` |
| POST | `/v1/api/contracts/{contract_uuid}/invoices` | resource | `api:contract:invoice` | `api:contract:invoice:create` |
| GET | `/v1/api/contracts/{contract_uuid}/invoices/{invoice_uuid}` | resource | `api:contract:invoice` | `api:contract:invoice:read` |
| PATCH | `/v1/api/contracts/{contract_uuid}/invoices/{invoice_uuid}` | resource | `api:contract:invoice` | `api:contract:invoice:update` |
| DELETE | `/v1/api/contracts/{contract_uuid}/invoices/{invoice_uuid}` | resource | `api:contract:invoice` | `api:contract:invoice:delete` |

All endpoints are protected — there are no public routes.

---

## OAuth2 Scopes and Permissions

Scopes identify the resource area a token is granted access to. Permissions identify the specific action allowed within that area. `RouteAuthorization` checks both — the token must carry the required scope **and** the required permission.

### Scopes

| Scope | Resource area |
|-------|---------------|
| `api:customer` | Customer endpoints |
| `api:customer:address` | Customer address sub-endpoints |
| `api:contract` | Contract endpoints |
| `api:contract:invoice` | Contract invoice sub-endpoints |

### Permissions

| Permission | Grants |
|------------|--------|
| `api:customer:read` | `GET /customers`, `GET /customers/{id}` |
| `api:customer:create` | `POST /customers` |
| `api:customer:update` | `PATCH /customers/{id}` |
| `api:customer:delete` | `DELETE /customers/{id}` |
| `api:customer:address:read` | `GET /customers/{id}/addresses`, `GET .../addresses/{id}` |
| `api:customer:address:create` | `POST /customers/{id}/addresses` |
| `api:customer:address:update` | `PATCH .../addresses/{id}` |
| `api:customer:address:delete` | `DELETE .../addresses/{id}` |
| `api:contract:read` | `GET /contracts`, `GET /contracts/{uuid}` |
| `api:contract:create` | `POST /contracts` |
| `api:contract:update` | `PATCH /contracts/{uuid}` |
| `api:contract:delete` | `DELETE /contracts/{uuid}` |
| `api:contract:invoice:read` | `GET /contracts/{uuid}/invoices`, `GET .../invoices/{uuid}` |
| `api:contract:invoice:create` | `POST /contracts/{uuid}/invoices` |
| `api:contract:invoice:update` | `PATCH .../invoices/{uuid}` |
| `api:contract:invoice:delete` | `DELETE .../invoices/{uuid}` |

---

## File Structure

Files marked `[generated]` are written by `DocBlockGenerator::run()` and must not be edited by hand. Everything else is written manually.

```
Tests/Integration/RealWorld/
│
├── Bootstrap/
│   ├── ApiFactory.php              # Builds Apivalk with all routes + full middleware stack
│   ├── InMemoryCache.php           # Array-backed CacheInterface for deterministic tests
│   ├── TestAuthenticator.php       # Token → identity mapping (no JWT crypto)
│   └── RequestTrait.php            # Superglobal setup + apivalk->run() helper
│
├── Customer/
│   │   # One request class per controller — required so DocBlockGenerator writes the
│   │   # correct body/path/sort/filter docblock for each operation independently.
│   ├── Request/
│   │   ├── CustomerListRequest.php       # defines filtering, sorting, pagination in getDocumentation()
│   │   ├── CustomerCreateRequest.php     # defines body properties in getDocumentation()
│   │   ├── CustomerViewRequest.php       # defines path property in getDocumentation()
│   │   ├── CustomerUpdateRequest.php     # defines body + path properties
│   │   ├── CustomerDeleteRequest.php     # defines path property
│   │   └── Shape/                        # [generated]
│   │       ├── CustomerListRequestBody.php
│   │       ├── CustomerListRequestPath.php
│   │       ├── CustomerListRequestQuery.php
│   │       ├── CustomerListRequestSorting.php
│   │       ├── CustomerListRequestFiltering.php
│   │       └── ... (one set per request class)
│   ├── CustomerListResponse.php
│   ├── CustomerViewResponse.php
│   ├── CustomerCreatedResponse.php
│   ├── CustomerUpdatedResponse.php
│   ├── ListCustomersController.php
│   ├── ViewCustomerController.php
│   ├── CreateCustomerController.php
│   ├── UpdateCustomerController.php
│   └── DeleteCustomerController.php
│
├── Customer/Address/
│   ├── Request/
│   │   ├── AddressListRequest.php
│   │   ├── AddressCreateRequest.php
│   │   ├── AddressViewRequest.php
│   │   ├── AddressUpdateRequest.php
│   │   ├── AddressDeleteRequest.php
│   │   └── Shape/                        # [generated]
│   ├── AddressListResponse.php
│   ├── AddressViewResponse.php
│   ├── AddressCreatedResponse.php
│   ├── AddressUpdatedResponse.php
│   ├── ListAddressesController.php
│   ├── ViewAddressController.php
│   ├── CreateAddressController.php
│   ├── UpdateAddressController.php
│   └── DeleteAddressController.php
│
├── Contract/
│   ├── ContractResource.php              # manually written; [generated] @property docblocks added to it
│   ├── Request/                          # [generated] entire directory
│   │   ├── ContractListRequest.php
│   │   ├── ContractCreateRequest.php
│   │   ├── ContractViewRequest.php
│   │   ├── ContractUpdateRequest.php
│   │   ├── ContractDeleteRequest.php
│   │   └── Shape/
│   │       ├── ContractListPath.php
│   │       ├── ContractListSorting.php
│   │       ├── ContractListFiltering.php
│   │       └── ... (path/sorting/filtering per controller)
│   ├── ListContractsController.php
│   ├── ViewContractController.php
│   ├── CreateContractController.php
│   ├── UpdateContractController.php
│   └── DeleteContractController.php
│
├── Contract/Invoice/
│   ├── InvoiceResource.php               # manually written; [generated] @property docblocks added
│   ├── Request/                          # [generated] entire directory
│   │   ├── InvoiceListRequest.php
│   │   ├── InvoiceCreateRequest.php
│   │   ├── InvoiceViewRequest.php
│   │   ├── InvoiceUpdateRequest.php
│   │   ├── InvoiceDeleteRequest.php
│   │   └── Shape/
│   └── ... (controllers)
│
└── Tests/
    ├── CustomerIntegrationTest.php
    ├── AddressIntegrationTest.php
    ├── ContractIntegrationTest.php
    ├── InvoiceIntegrationTest.php
    ├── MiddlewareIntegrationTest.php
    └── ValidationIntegrationTest.php
```

---

## Data Models

### Customer (non-resource)

`customer_id` is an **auto-increment integer** (not a UUID) — this intentionally mixes the ID styles across resources.

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `customer_id` | integer | read-only | auto-increment, assigned by system |
| `first_name` | string | yes | 1–100 chars |
| `last_name` | string | yes | 1–100 chars |
| `email` | string | yes | must contain `@` and a `.` after it |
| `phone` | string | no | max 20 chars, digits/spaces/`+`/`-`/`()` only |
| `status` | enum | yes | `active`, `inactive`, `pending` |
| `created_at` | datetime | read-only | |
| `updated_at` | datetime | read-only | |

**Body validators (enforced by RequestValidationMiddleware):**
- `first_name` — `StringProperty` with `minLength: 1`, `maxLength: 100`
- `last_name` — `StringProperty` with `minLength: 1`, `maxLength: 100`
- `email` — `StringProperty` with regex pattern `/.+@.+\..+/`
- `phone` — `StringProperty` with `maxLength: 20`, optional
- `status` — `EnumProperty(['active', 'inactive', 'pending'])`

**Path parameter validators:**
- `customer_id` — `IntegerProperty` with `minimum: 1` (no zero or negative IDs)

**List filters:**
- `first_name` — `StringFilter::like()`
- `last_name` — `StringFilter::like()`
- `email` — `StringFilter::equals()`
- `status` — `EnumFilter::equals()`

**List sorting** (defaults: `last_name ASC`, `created_at DESC`; optional: `first_name`, `email`)

**Pagination:** page-based, max 100

---

### Address (non-resource, nested under Customer)

`address_uuid` is a UUID v4 string. `customer_id` is the parent integer ID, taken from the path.

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `address_uuid` | string (UUID v4) | read-only | auto-generated |
| `customer_id` | integer | read-only | from `{customer_id}` path param |
| `street` | string | yes | 1–255 chars |
| `city` | string | yes | 1–100 chars |
| `zip` | string | yes | 1–20 chars |
| `country` | string | yes | ISO 3166-1 alpha-2 — exactly 2 uppercase chars |
| `type` | enum | yes | `billing`, `shipping`, `both` |
| `is_primary` | boolean | no | default false |
| `created_at` | datetime | read-only | |

**Body validators (enforced by RequestValidationMiddleware):**
- `street` — `StringProperty` with `minLength: 1`, `maxLength: 255`
- `city` — `StringProperty` with `minLength: 1`, `maxLength: 100`
- `zip` — `StringProperty` with `minLength: 1`, `maxLength: 20`
- `country` — `StringProperty` with `minLength: 2`, `maxLength: 2`
- `type` — `EnumProperty(['billing', 'shipping', 'both'])`
- `is_primary` — `BooleanProperty`, optional

**Path parameter validators:**
- `customer_id` — `IntegerProperty` with `minimum: 1`
- `address_uuid` — `StringProperty` with UUID v4 regex pattern

**List filters:**
- `city` — `StringFilter::like()`
- `country` — `StringFilter::equals()`
- `type` — `EnumFilter::equals()`
- `is_primary` — `BooleanFilter::equals()`

**List sorting** (defaults: `city ASC`; optional: `country`, `type`, `created_at`)

**Pagination:** page-based, max 50

---

### Contract (resource — extends AbstractResource)

`contract_uuid` is a UUID v4 string. `customer_id` is the integer ID of the owning customer.

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `contract_uuid` | string (UUID v4) | read-only | auto-generated |
| `customer_id` | integer | yes (create) | integer customer ID; excluded on UPDATE |
| `title` | string | yes | 1–255 chars |
| `value` | float | yes | must be > 0.0 |
| `currency` | string | no | ISO 4217 — exactly 3 uppercase chars; default `EUR` |
| `status` | enum | yes | `draft`, `active`, `expired`, `terminated` |
| `start_date` | date | yes | `YYYY-MM-DD` format |
| `end_date` | date | no | `YYYY-MM-DD` format |
| `notes` | string | no | max 5000 chars |
| `created_at` | datetime | read-only | excluded on CREATE/UPDATE |
| `updated_at` | datetime | read-only | excluded on CREATE/UPDATE |

Modes: `contract_uuid`, `created_at`, `updated_at` excluded on CREATE; `customer_id`, `contract_uuid`, `created_at`, `updated_at` excluded on UPDATE.

**Body validators (enforced by RequestValidationMiddleware):**
- `customer_id` — `IntegerProperty` with `minimum: 1`
- `title` — `StringProperty` with `minLength: 1`, `maxLength: 255`
- `value` — `FloatProperty` with `minimum: 0.01`
- `currency` — `StringProperty` with `minLength: 3`, `maxLength: 3`, optional
- `status` — `EnumProperty(['draft', 'active', 'expired', 'terminated'])`
- `start_date` — `DateProperty`
- `end_date` — `DateProperty`, optional
- `notes` — `StringProperty` with `maxLength: 5000`, optional

**Path parameter validators:**
- `contract_uuid` — `StringProperty` with UUID v4 regex pattern

**List filters:**
- `customer_id` — `IntegerFilter::equals()`
- `status` — `EnumFilter::in()`
- `title` — `StringFilter::like()`
- `value` — `FloatFilter::greaterThan()` and `FloatFilter::lessThan()`
- `start_date` — `DateFilter::greaterThan()` and `DateFilter::lessThan()`

**List sorting** (defaults: `title ASC`, `start_date DESC`; optional: `value`, `status`)

**Pagination:** cursor-based, max 50

---

### Invoice (resource — nested under Contract)

Both `invoice_uuid` and `contract_uuid` are UUID v4 strings.

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `invoice_uuid` | string (UUID v4) | read-only | auto-generated |
| `contract_uuid` | string (UUID v4) | read-only | from `{contract_uuid}` path param |
| `invoice_number` | string | read-only | auto-generated (e.g. `INV-2024-0001`) |
| `amount` | float | yes | must be > 0.0 |
| `tax_rate` | float | yes | 0.0–100.0 inclusive |
| `total_amount` | float | read-only | computed: `amount * (1 + tax_rate / 100)` |
| `status` | enum | yes | `draft`, `sent`, `paid`, `overdue`, `cancelled` |
| `due_date` | date | yes | `YYYY-MM-DD` format |
| `paid_at` | datetime | no | |
| `created_at` | datetime | read-only | |

`invoice_uuid`, `contract_uuid`, `invoice_number`, `total_amount`, `created_at` excluded on CREATE/UPDATE. `contract_uuid` auto-populated from path on UPDATE.

**Body validators (enforced by RequestValidationMiddleware):**
- `amount` — `FloatProperty` with `minimum: 0.01`
- `tax_rate` — `FloatProperty` with `minimum: 0.0`, `maximum: 100.0`
- `status` — `EnumProperty(['draft', 'sent', 'paid', 'overdue', 'cancelled'])`
- `due_date` — `DateProperty`
- `paid_at` — `DateTimeProperty`, optional

**Path parameter validators:**
- `contract_uuid` — `StringProperty` with UUID v4 regex pattern
- `invoice_uuid` — `StringProperty` with UUID v4 regex pattern

**List filters:**
- `status` — `EnumFilter::equals()`
- `amount` — `FloatFilter::greaterThan()` and `FloatFilter::lessThan()`
- `due_date` — `DateFilter::greaterThan()` and `DateFilter::lessThan()`
- `paid_at` — `DateTimeFilter::greaterThan()` and `DateTimeFilter::lessThan()`

**List sorting** (defaults: `due_date ASC`, `created_at DESC`; optional: `amount`, `status`)

**Pagination:** offset-based, max 100

---

## DocBlock Generation

`DocBlockGenerator::run(string $apiDirectory, string $namespace)` scans every controller class under `$apiDirectory`, then does two different things depending on whether the controller is resource-based or not.

### Resource controllers (Contract, Invoice)

For each resource controller the generator:

1. **Writes `@property` docblocks onto the Resource class** (`ContractResource`, `InvoiceResource`) based on properties registered in `init()`. Run this once; subsequent runs rewrite the block.
2. **Creates (or rewrites) a per-controller request file** in a `Request/` subdirectory next to the controllers, named `{ResourceName}{Mode}Request.php` (e.g. `ContractListRequest.php`). The file extends `ResourceRequest` and is stamped with `@method` docblocks for `path()`, `sorting()`, `filtering()`, and `paginator()`.
3. **Writes Shape files** into `Request/Shape/` for path, sorting, and filtering — one PHP class per shape.

These files must not exist yet when `DocBlockGenerator` runs for the first time (it creates them). On subsequent runs it rewrites the docblocks in-place.

**The controllers themselves never reference these generated request classes** — `AbstractResourceController` inherits `getRequestClass()` returning `ResourceRequest::class`. The generated `ContractListRequest.php` exists purely for IDE type inference and PHPStan; at runtime `ResourceRequest` is instantiated.

### Non-resource controllers (Customer, Address)

For each non-resource controller the generator reads `getRequestClass()` and:

1. **Rewrites the `@method` docblocks on that request class file** for `body()`, `path()`, `query()`, `sorting()`, `filtering()`, and `paginator()` — derived from `getDocumentation()` on the request class plus `pathProperty()` / `filtering()` / `sorting()` on the route.
2. **Writes Shape files** into a `Shape/` subdirectory next to the request class — one PHP class per shape (body, path, query, sorting, filtering).

**Because the generator processes one controller at a time and overwrites the target file, each non-resource controller must have its own dedicated request class.** If `ListCustomersController` and `CreateCustomerController` both pointed to the same `CustomerRequest`, the second run would erase the first controller's docblock. The plan therefore uses one request class per operation (`CustomerListRequest`, `CustomerCreateRequest`, etc.).

### How to invoke

There is no composer script for this yet. Call it directly with a small bootstrap script or from a test helper:

```php
<?php
// scripts/generate-docblocks.php
require_once __DIR__ . '/vendor/autoload.php';

(new \apivalk\apivalk\Documentation\DocBlock\DocBlockGenerator())->run(
    __DIR__ . '/Tests/Integration/RealWorld',
    'Tests\\Integration\\RealWorld'   // PSR-4 namespace root for that directory
);
```

Run it whenever you add a new property to a Resource, add a path param to a route, or change body fields in a request's `getDocumentation()`:

```bash
php scripts/generate-docblocks.php
```

### When to run in the implementation sequence

Run `DocBlockGenerator` after each of these milestones:

- After writing all 5 Customer request classes + controllers → generates Customer Shape files
- After writing all 5 Address request classes + controllers → generates Address Shape files
- After writing `ContractResource` + all 5 Contract controllers → generates Contract request files + Shape files + Resource `@property` block
- After writing `InvoiceResource` + all 5 Invoice controllers → generates Invoice request files + Shape files + Resource `@property` block
- Re-run any time a path param, filter, sorting, or body property is added or renamed

PHPStan must be run **after** generation, not before, since it will reference the generated Shape classes.

---

## Bootstrap Infrastructure

### `Bootstrap/InMemoryCache.php`

Implements `apivalk\apivalk\Cache\CacheInterface` — this is the **framework's own interface**, not PSR-6 or PSR-16. Required methods:

```php
get(string $key): ?CacheItem
set(CacheItem $cacheItem): bool
delete(string $key): bool
clear(): void
has(string $key): bool
getDefaultCacheLifetime(): int
```

`CacheItem` is `apivalk\apivalk\Cache\CacheItem`. It carries `key`, `value`, `ttl` (seconds, nullable), and `createdAt` (UTC `\DateTime`). The `isExpired()` check must compare `createdAt + ttl` against current UTC time — the rate limiter relies on this for window expiry.

Store items in a plain `array<string, CacheItem>`. Each test must instantiate a **fresh** `InMemoryCache` so route index cache and rate limit counters never bleed between tests.

### `Bootstrap/TestAuthenticator.php`

Implements `AuthenticatorInterface`. Maps string tokens to `AbstractAuthIdentity` instances without any JWT validation. Each identity carries both scopes (resource areas) and permissions (actions).

| Token | Scopes | Permissions |
|-------|--------|-------------|
| `'admin-token'` | all 4 scopes | all 16 permissions |
| `'read-only-token'` | all 4 scopes | 4 `*:read` permissions only |
| `'customer-token'` | `api:customer` only | `api:customer:read/create/update/delete` |
| `'contract-token'` | `api:contract`, `api:contract:invoice` | all 8 contract + invoice permissions |
| `'no-scope-token'` | (none) | (none) |
| Any other / absent | — | `GuestAuthIdentity` (unauthenticated) |

`'customer-token'` deliberately lacks scope `api:customer:address`, so address endpoint tests that expect 403 work without needing a special token.

### `Bootstrap/ApiFactory.php`

Single static factory that wires everything together:

```php
class ApiFactory
{
    public static function create(
        ?CacheInterface $cache = null,
        ?AuthenticatorInterface $authenticator = null,
        int $rateLimitMax = 1000,         // use low values in rate-limit tests
        int $rateLimitWindow = 60
    ): Apivalk {
        $cache = $cache ?? new InMemoryCache();
        $authenticator = $authenticator ?? new TestAuthenticator();

        // Router takes a ClassLocator (directory + namespace) not an array of classes.
        // ClassLocator scans the directory recursively and discovers all controller classes
        // by resolving their FQCN from file paths. The directory must contain ONLY controllers
        // for this integration suite — Bootstrap/, Response/, Resource/ files are fine because
        // ClassLocator filters for AbstractApivalkController subclasses only.
        $classLocator = new ClassLocator(
            __DIR__ . '/../',                          // Tests/Integration/RealWorld/
            'Tests\\Integration\\RealWorld'            // PSR-4 namespace root for that directory
        );

        $router = new Router($classLocator, $cache);

        $config = new ApivalkConfiguration($router);
        $stack  = $config->getMiddlewareStack();

        // Order matters — first add() = first executed (MiddlewareStack reverses before chaining)
        $stack->add(new AuthenticationMiddleware($authenticator));
        $stack->add(new SecurityMiddleware());
        $stack->add(new RateLimitMiddleware($cache));
        $stack->add(new SanitizeMiddleware());
        $stack->add(new RequestValidationMiddleware());

        return new Apivalk($config);
    }
}
```

### `Bootstrap/RequestTrait.php`

PHPUnit trait mixed into every test class. Provides:

```php
trait RequestTrait
{
    private Apivalk $apivalk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apivalk = ApiFactory::create(new InMemoryCache());
        $this->resetSuperglobals();
    }

    protected function tearDown(): void
    {
        $this->resetSuperglobals();
        parent::tearDown();
    }

    private function resetSuperglobals(): void
    {
        $_SERVER = [
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REMOTE_ADDR'     => '127.0.0.1',
        ];
        $_GET   = [];
        $_POST  = [];
        $_FILES = [];
    }

    protected function makeRequest(
        string $method,
        string $path,
        array  $query  = [],
        array  $body   = [],
        string $token  = null,
        string $ip     = '127.0.0.1'
    ): AbstractApivalkResponse {
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI']    = $path . ($query ? '?' . http_build_query($query) : '');
        $_SERVER['REMOTE_ADDR']    = $ip;

        if ($token !== null) {
            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $_GET  = $query;
        $_POST = $body;
        // BodyParameterPopulationStrategy reads php://input (JSON) first, then merges $_POST.
        // php://input is empty in a PHPUnit process, so $_POST is the effective body source.
        // All test body fields are scalar — no nested objects — so this works without JSON encoding.

        return $this->apivalk->run();
    }
}
```

---

## Controller Implementation Notes

### Non-Resource Controllers (Customer, Address)

Each controller extends `AbstractApivalkController` and:

- Defines `getRoute()` with `routeAuthorization()`, and for list routes: `filtering()`, `sorting()`, `pagination()`, `rateLimit()`.
- Path parameter routes call `->pathProperty(...)` for each `{placeholder}`. Use `IntegerProperty` for `customer_id` (minimum: 1) and `StringProperty` with the UUID pattern for `address_uuid`.
- `getRequestClass()` returns the **operation-specific** request class (`CustomerListRequest::class`, `CustomerCreateRequest::class`, etc.) — one class per controller, never shared.
- `getResponseClasses()` returns all possible responses including error types.
- `__invoke()` reads from `$request->body()`, `$request->path()`, `$request->filtering()`, `$request->sorting()`, `$request->paginator()` and returns a hardcoded fixture response so middleware tests work without a real database.

Each request class implements `getDocumentation()` returning an `ApivalkRequestDocumentation` with the properties relevant to that operation — body fields for write operations, nothing for delete. `RequestValidationMiddleware` reads this documentation to validate incoming data. After all controllers are written, `DocBlockGenerator` rewrites the `@method` docblocks and generates the `Shape/` classes for IDE/PHPStan support.

### Resource Controllers (Contract, Invoice)

- List controllers extend `AbstractListResourceController`. `getResourceClass()` returns `ContractResource::class` or `InvoiceResource::class`. The route is defined by the controller, not the resource. `__invoke()` returns `new ResourceListResponse([$resource->toArray(Mode::LIST)])`.
- Create controllers extend `AbstractCreateResourceController`. `__invoke()` calls `$this->getResource($request)` which builds from body; returns `new ResourceCreatedResponse($resource)`.
- View controllers extend `AbstractViewResourceController`. `__invoke()` reads `$request->path()->get('contract_uuid')`, returns `new ResourceViewResponse($resource)` or `new NotFoundApivalkResponse()`.
- Update controllers extend `AbstractUpdateResourceController`. `getResource()` auto-populates `contract_uuid`/`invoice_uuid` from path into the resource. Returns `new ResourceUpdatedResponse($resource)`.
- Delete controllers extend `AbstractDeleteResourceController`. Returns `new DeletedApivalkResponse()`.

### Nested Path Parameters

Invoice routes register two path properties:
```php
Route::get('/v1/api/contracts/{contract_uuid}/invoices')
    ->pathProperty(new StringProperty('contract_uuid', 'Contract UUID'))
    ->filtering(InvoiceResource::availableFilters())
    ->sorting(InvoiceResource::availableSortings())
    ->pagination(Pagination::offset()->setMaxLimit(100))
    ->routeAuthorization(new RouteAuthorization('bearer', ['api:contract:invoice'], ['api:contract:invoice:read']))
    ->rateLimit(new IpRateLimit('list-invoices', 60, 60));

Route::get('/v1/api/contracts/{contract_uuid}/invoices/{invoice_uuid}')
    ->pathProperty(new StringProperty('contract_uuid', 'Contract UUID'))
    ->pathProperty(new StringProperty('invoice_uuid', 'Invoice UUID'))
    ->routeAuthorization(new RouteAuthorization('bearer', ['api:contract:invoice'], ['api:contract:invoice:read']));
```

All `RouteAuthorization` calls follow the same pattern: `new RouteAuthorization('bearer', ['<scope>'], ['<permission>'])`. For example, the list-customers route uses `new RouteAuthorization('bearer', ['api:customer'], ['api:customer:read'])` and the create-customer route uses `new RouteAuthorization('bearer', ['api:customer'], ['api:customer:create'])`.

`ListInvoicesController::__invoke()` reads `$request->path()->get('contract_uuid')` to scope the listing.

---

## Path Parameter Validators

`RequestValidationMiddleware` validates path parameters against the `pathProperty()` definitions on each route. The table below summarises every path param, its type, its constraint, and what an invalid value looks like in a test.

| Param | Route(s) | Property type | Constraint | Invalid examples → 422 | Valid example |
|-------|----------|---------------|------------|------------------------|---------------|
| `customer_id` | all `/customers/{customer_id}` routes | `IntegerProperty` | `minimum: 1` | `0`, `-1`, `abc`, `1.5` | `42` |
| `address_uuid` | all `.../addresses/{address_uuid}` routes | `StringProperty` | UUID v4 pattern | `abc`, `123`, `not-a-uuid`, `` (empty) | `550e8400-e29b-41d4-a716-446655440000` |
| `contract_uuid` | all `/contracts/{contract_uuid}` routes | `StringProperty` | UUID v4 pattern | `abc`, `123`, `not-a-uuid` | `6ba7b810-9dad-11d1-80b4-00c04fd430c8` |
| `invoice_uuid` | all `.../invoices/{invoice_uuid}` routes | `StringProperty` | UUID v4 pattern | `abc`, `0`, `not-a-uuid` | `f47ac10b-58cc-4372-a567-0e02b2c3d479` |

### Route definition examples

```php
// customer_id as integer path param with minimum
Route::get('/v1/api/customers/{customer_id}')
    ->pathProperty((new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1))
    ->routeAuthorization(new RouteAuthorization('bearer', ['api:customer'], ['api:customer:read']));

// address_uuid as UUID string path param
Route::get('/v1/api/customers/{customer_id}/addresses/{address_uuid}')
    ->pathProperty((new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1))
    ->pathProperty((new StringProperty('address_uuid', 'Address UUID v4'))->setPattern(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i'
    ))
    ->routeAuthorization(new RouteAuthorization('bearer', ['api:customer:address'], ['api:customer:address:read']));

// contract_uuid as UUID string path param
Route::get('/v1/api/contracts/{contract_uuid}')
    ->pathProperty((new StringProperty('contract_uuid', 'Contract UUID v4'))->setPattern(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i'
    ))
    ->routeAuthorization(new RouteAuthorization('bearer', ['api:contract'], ['api:contract:read']));
```

### Sentinel IDs for 404 tests

Since controllers return fixture data, they must distinguish "path param valid but record not found" from "path param invalid":

| Resource | Valid fixture ID (→ 200) | Not-found sentinel (→ 404) |
|----------|--------------------------|----------------------------|
| Customer | `42` | `99999` |
| Address | `550e8400-e29b-41d4-a716-446655440000` | `00000000-0000-4000-8000-000000000000` |
| Contract | `6ba7b810-9dad-11d1-80b4-00c04fd430c8` | `00000000-0000-4000-8000-000000000000` |
| Invoice | `f47ac10b-58cc-4372-a567-0e02b2c3d479` | `00000000-0000-4000-8000-000000000001` |

Path params that are structurally wrong (wrong type, wrong format) return **422** from `RequestValidationMiddleware` and never reach the controller.

---

## Test Classes

### `Tests/CustomerIntegrationTest.php`

**Setup:** fresh `ApiFactory::create()` per test via `RequestTrait`.

```
--- List ---
testListCustomers_withAdminToken_returns200
testListCustomers_withReadOnlyToken_returns200
testListCustomers_withoutToken_returns401
testListCustomers_withNoScopeToken_returns403
testListCustomers_withCustomerToken_returns200      # api:customer:read present
testListCustomers_withContractToken_returns403      # api:customer:read absent

testListCustomers_filterByStatusActive_returns200WithMatchingData
testListCustomers_filterByFirstNameLike_returns200
testListCustomers_filterByEmail_returns200
testListCustomers_invalidStatusValue_returns422     # EnumFilter rejects unknown value
testListCustomers_unknownFilterField_returns422
testListCustomers_unknownSortField_returns422

testListCustomers_sortByLastNameAsc_returns200
testListCustomers_sortByCreatedAtDesc_returns200
testListCustomers_sortByMultipleFields_returns200
testListCustomers_defaultSortApplied_noOrderByParam_returns200

testListCustomers_pagePage2Limit5_returns200
testListCustomers_limitExceedsMax_clampsToMax        # max 100
testListCustomers_invalidPageParam_returns422

--- Create ---
testCreateCustomer_validBody_returns201
testCreateCustomer_withoutToken_returns401
testCreateCustomer_withReadOnlyToken_returns403          # no api:customer:create permission
testCreateCustomer_withCustomerToken_returns201          # has api:customer:create permission
testCreateCustomer_missingFirstName_returns422
testCreateCustomer_missingLastName_returns422
testCreateCustomer_missingEmail_returns422
testCreateCustomer_missingStatus_returns422
testCreateCustomer_invalidStatusValue_returns422
testCreateCustomer_firstNameTooLong_returns422           # > 100 chars → StringProperty maxLength
testCreateCustomer_lastNameTooLong_returns422
testCreateCustomer_emptyFirstName_returns422             # "" → StringProperty minLength: 1
testCreateCustomer_emptyLastName_returns422
testCreateCustomer_invalidEmailNoAt_returns422           # "notanemail" → pattern fails
testCreateCustomer_invalidEmailNoDot_returns422          # "foo@bar" → pattern fails
testCreateCustomer_phoneTooLong_returns422               # > 20 chars
testCreateCustomer_multipleMissingFields_returns422WithAllFieldNames
testCreateCustomer_htmlInFirstName_sanitizedInResponse   # SanitizeMiddleware

--- View ---
testViewCustomer_withAdminToken_returns200
testViewCustomer_withoutToken_returns401
testViewCustomer_withNoScopeToken_returns403
testViewCustomer_unknownCustomerId_returns404            # customer_id = 99999 → controller returns 404
testViewCustomer_customerIdZero_returns422               # 0 < minimum: 1 → validation fails
testViewCustomer_customerIdNegative_returns422           # -1 → validation fails
testViewCustomer_customerIdNonInteger_returns422         # "abc" → wrong type
testViewCustomer_pathParameterAvailableInController      # customer_id = 42 → controller reads it

--- Update ---
testUpdateCustomer_validBody_returns200
testUpdateCustomer_withoutToken_returns401
testUpdateCustomer_withReadOnlyToken_returns403
testUpdateCustomer_missingRequiredField_returns422
testUpdateCustomer_firstNameTooLong_returns422
testUpdateCustomer_emptyLastName_returns422
testUpdateCustomer_pathCustomerIdPropagated
testUpdateCustomer_customerIdZero_returns422
testUpdateCustomer_customerIdNonInteger_returns422
testUpdateCustomer_unknownCustomerId_returns404          # customer_id = 99999

--- Delete ---
testDeleteCustomer_withAdminToken_returns204
testDeleteCustomer_withoutToken_returns401
testDeleteCustomer_withReadOnlyToken_returns403
testDeleteCustomer_unknownCustomerId_returns404
testDeleteCustomer_customerIdZero_returns422
testDeleteCustomer_customerIdNonInteger_returns422
```

---

### `Tests/AddressIntegrationTest.php`

```
--- List ---
testListAddresses_withAdminToken_returns200
testListAddresses_withoutToken_returns401
testListAddresses_withCustomerToken_returns403       # needs api:customer:address:read, not api:customer:read
testListAddresses_withAddressReadScope_returns200
testListAddresses_pathCustomerIdAvailableInController

testListAddresses_filterByCityLike_returns200
testListAddresses_filterByCountryEquals_returns200
testListAddresses_filterByType_returns200
testListAddresses_filterByIsPrimaryTrue_returns200
testListAddresses_invalidTypeValue_returns422
testListAddresses_unknownFilterField_returns422

testListAddresses_sortByCityAsc_returns200
testListAddresses_sortByCountry_returns200
testListAddresses_pagePage1Limit10_returns200

--- Create ---
testCreateAddress_validBody_returns201
testCreateAddress_withoutToken_returns401
testCreateAddress_withAddressReadScope_returns403       # needs create permission, not read
testCreateAddress_pathCustomerIdPropagatedToResponse
testCreateAddress_missingStreet_returns422
testCreateAddress_missingCity_returns422
testCreateAddress_missingZip_returns422
testCreateAddress_missingCountry_returns422
testCreateAddress_missingType_returns422
testCreateAddress_invalidType_returns422
testCreateAddress_countryTooLong_returns422             # 3+ chars → maxLength: 2
testCreateAddress_countryTooShort_returns422            # 1 char → minLength: 2
testCreateAddress_streetEmpty_returns422
testCreateAddress_streetTooLong_returns422              # > 255 chars
testCreateAddress_cityEmpty_returns422
testCreateAddress_zipTooLong_returns422                 # > 20 chars
testCreateAddress_customerIdZero_returns422             # path: customer_id = 0 → IntegerProperty minimum: 1
testCreateAddress_customerIdNonInteger_returns422       # path: "abc"
testCreateAddress_htmlInStreet_sanitized

--- View ---
testViewAddress_withAdminToken_returns200
testViewAddress_withoutToken_returns401
testViewAddress_bothPathParamsPresentInController
testViewAddress_unknownAddressUuid_returns404           # valid UUID but not found
testViewAddress_customerIdZero_returns422               # path: customer_id = 0
testViewAddress_customerIdNonInteger_returns422         # path: "abc"
testViewAddress_addressUuidNotUuid_returns422           # "not-a-uuid"
testViewAddress_addressUuidEmpty_returns422

--- Update ---
testUpdateAddress_validBody_returns200
testUpdateAddress_withoutToken_returns401
testUpdateAddress_wrongScope_returns403
testUpdateAddress_pathParamsPropagated
testUpdateAddress_missingRequiredField_returns422
testUpdateAddress_countryTooLong_returns422
testUpdateAddress_customerIdZero_returns422
testUpdateAddress_addressUuidNotUuid_returns422

--- Delete ---
testDeleteAddress_withAdminToken_returns204
testDeleteAddress_withoutToken_returns401
testDeleteAddress_wrongScope_returns403
testDeleteAddress_unknownAddressUuid_returns404
testDeleteAddress_customerIdZero_returns422
testDeleteAddress_addressUuidNotUuid_returns422
```

---

### `Tests/ContractIntegrationTest.php`

```
--- List ---
testListContracts_withAdminToken_returns200
testListContracts_withoutToken_returns401
testListContracts_withContractReadScope_returns200
testListContracts_withCustomerToken_returns403       # no api:contract:read

testListContracts_filterByCustomerId_returns200
testListContracts_filterByStatus_returns200
testListContracts_filterByStatusIn_returns200        # EnumFilter IN: ?filter[status][]=active&...
testListContracts_filterByTitleLike_returns200
testListContracts_filterByValueGreaterThan_returns200
testListContracts_filterByValueLessThan_returns200
testListContracts_filterByStartDateRange_returns200
testListContracts_unknownFilterField_returns422
testListContracts_invalidDateFormat_returns422

testListContracts_sortByValue_returns200
testListContracts_sortByStartDate_returns200
testListContracts_defaultSortApplied_returns200
testListContracts_unknownSortField_returns422

testListContracts_cursorPagination_returns200
testListContracts_cursorPaginationLimitExceedsMax_clampsTo50

--- Create ---
testCreateContract_validBody_returns201
testCreateContract_withoutToken_returns401
testCreateContract_withContractReadScope_returns403
testCreateContract_withContractCreateScope_returns201
testCreateContract_missingTitle_returns422
testCreateContract_missingValue_returns422
testCreateContract_missingCustomerId_returns422
testCreateContract_missingStatus_returns422
testCreateContract_missingStartDate_returns422
testCreateContract_invalidStatus_returns422
testCreateContract_invalidDateFormat_returns422
testCreateContract_valueZero_returns422              # FloatProperty minimum: 0.01
testCreateContract_valueNegative_returns422
testCreateContract_customerIdZero_returns422         # body: IntegerProperty minimum: 1
testCreateContract_customerIdNegative_returns422
testCreateContract_currencyTooShort_returns422       # 2 chars → minLength: 3
testCreateContract_currencyTooLong_returns422        # 4 chars → maxLength: 3
testCreateContract_titleEmpty_returns422             # "" → minLength: 1
testCreateContract_titleTooLong_returns422           # > 255 chars
testCreateContract_notesTooLong_returns422           # > 5000 chars
testCreateContract_resourceBuiltFromBody             # all fields populated into ContractResource
testCreateContract_readOnlyFieldsExcluded            # contract_uuid absent in CREATE mode response

--- View ---
testViewContract_withAdminToken_returns200
testViewContract_withoutToken_returns401
testViewContract_withContractReadScope_returns200
testViewContract_pathUuidAvailableInController
testViewContract_unknownContractUuid_returns404      # valid UUID but sentinel → controller returns 404
testViewContract_contractUuidNotUuid_returns422      # "abc" → StringProperty pattern fails
testViewContract_contractUuidEmpty_returns422

--- Update ---
testUpdateContract_validBody_returns200
testUpdateContract_withoutToken_returns401
testUpdateContract_withContractReadScope_returns403
testUpdateContract_emptyBody_returns200              # all resource body fields are optional on UPDATE
testUpdateContract_valueZero_returns422              # constraint still enforced when field IS present
testUpdateContract_valueNegative_returns422
testUpdateContract_titleTooLong_returns422           # constraint enforced when field IS present
testUpdateContract_contractUuidNotUuid_returns422    # path param always validated
testUpdateContract_pathUuidAutoPopulatedIntoResource  # AbstractUpdateResourceController auto-population
testUpdateContract_customerIdNotUpdatable             # excluded from UPDATE mode — absent in runtime doc

--- Delete ---
testDeleteContract_withAdminToken_returns204
testDeleteContract_withoutToken_returns401
testDeleteContract_withContractReadScope_returns403
testDeleteContract_withContractDeleteScope_returns204
testDeleteContract_unknownContractUuid_returns404
testDeleteContract_contractUuidNotUuid_returns422
```

---

### `Tests/InvoiceIntegrationTest.php`

```
--- List ---
testListInvoices_withAdminToken_returns200
testListInvoices_withoutToken_returns401
testListInvoices_withInvoiceReadScope_returns200
testListInvoices_withContractReadScope_returns403    # api:contract:invoice:read is separate
testListInvoices_contractUuidPresentInController

testListInvoices_filterByStatus_returns200
testListInvoices_filterByAmountGreaterThan_returns200
testListInvoices_filterByAmountLessThan_returns200
testListInvoices_filterByDueDateRange_returns200
testListInvoices_filterByPaidAtRange_returns200
testListInvoices_invalidDateTimeFormat_returns422

testListInvoices_sortByDueDateAsc_returns200
testListInvoices_sortByAmount_returns200
testListInvoices_defaultSortsApplied_returns200
testListInvoices_unknownSortField_returns422

testListInvoices_offsetPagination_returns200
testListInvoices_offsetPaginationLimitExceedsMax_clampsTo100

--- Create ---
testCreateInvoice_validBody_returns201
testCreateInvoice_withoutToken_returns401
testCreateInvoice_wrongScope_returns403
testCreateInvoice_contractUuidFromPathPropagated
testCreateInvoice_missingAmount_returns422
testCreateInvoice_missingTaxRate_returns422
testCreateInvoice_missingStatus_returns422
testCreateInvoice_missingDueDate_returns422
testCreateInvoice_amountZero_returns422              # FloatProperty minimum: 0.01
testCreateInvoice_amountNegative_returns422
testCreateInvoice_taxRateExactly0_returns201         # 0.0 is valid (inclusive)
testCreateInvoice_taxRateExactly100_returns201       # 100.0 is valid (inclusive)
testCreateInvoice_taxRateAbove100_returns422         # 100.01 → maximum: 100.0
testCreateInvoice_taxRateNegative_returns422         # -1.0 → minimum: 0.0
testCreateInvoice_invalidDueDateFormat_returns422    # "2024/12/31" → DateProperty rejects
testCreateInvoice_invalidStatus_returns422
testCreateInvoice_contractUuidNotUuid_returns422     # path: "abc"
testCreateInvoice_invoiceUuidNotRequired             # read-only field absent in CREATE mode
testCreateInvoice_totalAmountComputedInResponse      # assert total = amount * (1 + tax_rate/100)

--- View ---
testViewInvoice_withAdminToken_returns200
testViewInvoice_withoutToken_returns401
testViewInvoice_withInvoiceReadScope_returns200
testViewInvoice_bothPathParamsAvailableInController
testViewInvoice_unknownInvoiceUuid_returns404        # valid UUID but sentinel → 404
testViewInvoice_contractUuidNotUuid_returns422       # path: "abc"
testViewInvoice_invoiceUuidNotUuid_returns422        # path: "not-a-uuid"
testViewInvoice_bothPathUuidsInvalid_returns422      # both wrong — first error reported

--- Update ---
testUpdateInvoice_validBody_returns200
testUpdateInvoice_withoutToken_returns401
testUpdateInvoice_wrongScope_returns403
testUpdateInvoice_emptyBody_returns200               # all resource body fields are optional on UPDATE
testUpdateInvoice_amountZero_returns422              # constraint enforced when field IS present
testUpdateInvoice_taxRateAbove100_returns422
testUpdateInvoice_taxRateNegative_returns422
testUpdateInvoice_contractUuidNotUuid_returns422     # path param always validated
testUpdateInvoice_invoiceUuidNotUuid_returns422
testUpdateInvoice_contractUuidAutoPopulatedFromPath  # AbstractUpdateResourceController auto-population
testUpdateInvoice_invoiceUuidAutoPopulatedFromPath

--- Delete ---
testDeleteInvoice_withAdminToken_returns204
testDeleteInvoice_withoutToken_returns401
testDeleteInvoice_withInvoiceReadScope_returns403
testDeleteInvoice_withInvoiceDeleteScope_returns204
testDeleteInvoice_unknownInvoiceUuid_returns404
testDeleteInvoice_contractUuidNotUuid_returns422
testDeleteInvoice_invoiceUuidNotUuid_returns422
```

---

### `Tests/MiddlewareIntegrationTest.php`

Tests middleware in isolation or in cross-cutting scenarios.

```
--- AuthenticationMiddleware ---
testAuth_noAuthorizationHeader_guestIdentity
testAuth_validToken_setsAuthenticatedIdentity
testAuth_unknownToken_treatedAsGuest             # TestAuthenticator returns null → guest
testAuth_bearerCaseInsensitive                   # "BEARER token" also accepted
testAuth_malformedHeader_treatedAsGuest          # "Token abc" (wrong scheme)
testAuth_guestOnProtectedRoute_returns401        # SecurityMiddleware rejects guest

--- SecurityMiddleware ---
testSecurity_authenticated_correctScope_proceeds_returns200
testSecurity_authenticated_insufficientScope_returns403
testSecurity_authenticated_noScopes_returns403
testSecurity_authenticated_adminAllScopes_accessAll
testSecurity_unauthenticated_protectedEndpoint_returns401

--- RateLimitMiddleware ---
testRateLimit_firstRequest_returns200WithHeaders
testRateLimit_headersPresent_XRateLimitLimit
testRateLimit_headersPresent_XRateLimitRemaining
testRateLimit_headersPresent_XRateLimitReset
testRateLimit_underLimit_remainingDecrements
testRateLimit_atLimit_lastRequestSucceeds
testRateLimit_exceededLimit_returns429
testRateLimit_429Response_containsRetryAfterHeader
testRateLimit_differentIpAddresses_separateLimits  # 127.0.0.1 vs 10.0.0.1
testRateLimit_differentEndpoints_separateLimits    # list-customers vs list-contracts
testRateLimit_windowExpiry_allowsRequestsAgain     # InMemoryCache TTL check

Note: Use ApiFactory::create(cache: new InMemoryCache(), rateLimitMax: 3, rateLimitWindow: 60)
to keep these tests fast with a low limit.

--- SanitizeMiddleware ---
testSanitize_htmlTagsInBody_escapedBeforeControllerReceives
testSanitize_scriptTagInBody_escapedNotExecuted
testSanitize_ampersandInBody_escaped
testSanitize_htmlInQueryParam_escaped
testSanitize_xssAttemptInBody_neutralized
testSanitize_nonStringFields_untouched             # integers/booleans passed through
testSanitize_nestedBodyArray_allStringsEscaped

--- RequestValidationMiddleware ---
testValidation_missingRequiredBodyField_returns422WithFieldName
testValidation_wrongBodyFieldType_returns422
testValidation_missingRequiredPathParam_returns422
testValidation_wrongPathParamType_returns422
testValidation_invalidQueryParam_returns422
testValidation_multipleValidationErrors_allReturnedInSingleResponse
testValidation_validRequest_proceedsToController
testValidation_optionalFieldAbsent_doesNotReturn422
testValidation_extraBodyFields_ignoredNotRejected

--- Middleware ordering (full stack) ---
testMiddlewareOrder_authRunsBeforeSecurity           # guest → 401 not 403
testMiddlewareOrder_authRunsBeforeValidation         # unauthed returns 401 before 422
testMiddlewareOrder_rateLimitBeforeValidation        # rate limited returns 429 before 422
testMiddlewareOrder_sanitizeRunsBeforeValidation     # escaped values pass validation
```

---

### `Tests/ValidationIntegrationTest.php`

Regression battery for `RequestValidationMiddleware`. Uses admin-token throughout — these tests are about validators, not auth.

```
--- Integer path params (customer_id) ---
testPathInt_validId_proceeds                         # customer_id = 42 → 200
testPathInt_zero_returns422                          # customer_id = 0 → minimum: 1 violated
testPathInt_negative_returns422                      # customer_id = -1
testPathInt_nonInteger_returns422                    # "abc" → wrong type
testPathInt_float_returns422                         # "1.5" → wrong type
testPathInt_empty_returns422

--- UUID path params (contract_uuid) ---
testPathUuid_validUuid_proceeds                      # valid v4 UUID → 200
testPathUuid_notUuid_returns422                      # "abc" → pattern violated
testPathUuid_numericOnly_returns422                  # "12345678" → pattern violated
testPathUuid_v1Uuid_returns422                       # UUID v1 format is not v4 pattern
testPathUuid_empty_returns422

--- Double UUID path params (contract_uuid + invoice_uuid) ---
testPathDoubleUuid_bothValid_proceeds
testPathDoubleUuid_firstInvalid_returns422
testPathDoubleUuid_secondInvalid_returns422
testPathDoubleUuid_bothInvalid_returns422            # 422 with at least one error reported

--- Body string constraints (customer first_name / last_name) ---
testBodyString_withinLength_proceeds
testBodyString_empty_returns422                      # "" → minLength: 1
testBodyString_exactlyMaxLength_proceeds             # 100 chars → passes
testBodyString_overMaxLength_returns422              # 101 chars → maxLength: 100
testBodyString_onlyWhitespace_proceeds               # " " — length is 1, passes validation

--- Body email pattern (customer email) ---
testBodyEmail_validEmail_proceeds
testBodyEmail_missingAt_returns422
testBodyEmail_missingDomainDot_returns422            # "user@domain"
testBodyEmail_emptyString_returns422

--- Body enum constraint (customer status) ---
testBodyEnum_validValue_proceeds
testBodyEnum_invalidValue_returns422
testBodyEnum_caseSensitive_returns422                # "Active" ≠ "active"
testBodyEnum_emptyString_returns422

--- Body float constraints (contract value) ---
testBodyFloat_aboveMinimum_proceeds                  # 0.01 → passes
testBodyFloat_exactlyMinimum_proceeds                # minimum: 0.01 → passes
testBodyFloat_belowMinimum_returns422                # 0.0 → fails
testBodyFloat_negative_returns422

--- Body float range (invoice tax_rate 0.0–100.0) ---
testBodyFloatRange_minimum_proceeds                  # 0.0 → passes (inclusive)
testBodyFloatRange_maximum_proceeds                  # 100.0 → passes (inclusive)
testBodyFloatRange_aboveMaximum_returns422           # 100.01 → fails
testBodyFloatRange_belowMinimum_returns422           # -0.01 → fails

--- Body string fixed-length (contract currency, 3 chars) ---
testBodyFixedLength_exactlyRight_proceeds            # "EUR" → passes
testBodyFixedLength_tooShort_returns422              # "EU" → minLength: 3
testBodyFixedLength_tooLong_returns422               # "EURO" → maxLength: 3

--- Body string fixed-length (address country, 2 chars) ---
testBodyCountry_exactlyRight_proceeds                # "DE" → passes
testBodyCountry_tooShort_returns422                  # "D" → minLength: 2
testBodyCountry_tooLong_returns422                   # "DEU" → maxLength: 2

--- Missing required fields ---
testBodyRequired_missingOneField_returns422WithFieldName
testBodyRequired_missingMultipleFields_returns422WithAllFieldNames
testBodyRequired_optionalFieldAbsent_proceeds

--- Extra / unknown fields ---
testBodyExtra_unknownFieldPresent_ignored            # extra key doesn't cause 422

--- Filter validation ---
testFilter_validStringEquals_proceeds
testFilter_validEnumIn_proceeds
testFilter_validDateRange_proceeds
testFilter_invalidEnumValue_returns422
testFilter_unknownFilterField_returns422
testFilter_floatFilterNonNumeric_returns422          # ?filter[value]=abc → FloatFilter
testFilter_integerFilterNonNumeric_returns422        # ?filter[customer_id]=abc → IntegerFilter

--- Sort validation ---
testSort_validField_returns200
testSort_validMultipleFields_returns200
testSort_unknownField_returns422
testSort_mixedValidAndInvalid_returns422
testSort_ascDescPrefixSyntax_returns200              # ?order_by=+last_name,-created_at
```

---

## Implementation Order

1. **`Bootstrap/InMemoryCache.php`** — needed by everything
2. **`Bootstrap/TestAuthenticator.php`** — needed by ApiFactory
3. **`Bootstrap/RequestTrait.php`** — needed by all tests
4. **5 Customer request classes** (`CustomerListRequest` … `CustomerDeleteRequest`) — each implements `getDocumentation()` with the correct body/path/query properties for its operation
5. **Customer response classes + 5 controllers**
6. **Run `DocBlockGenerator`** → writes `@method` docblocks + Shape files into `Customer/Request/Shape/`
7. **`Tests/CustomerIntegrationTest.php`** — validates the non-resource CRUD baseline
8. **5 Address request classes** — same pattern, include `customer_id` and `address_uuid` path properties
9. **Address response classes + 5 controllers**
10. **Run `DocBlockGenerator`** → writes Address Shape files
11. **`Tests/AddressIntegrationTest.php`**
12. **`Contract/ContractResource.php`** — `init()` with all properties, `availableFilters()`, `availableSortings()`
13. **5 Contract controllers** (no request classes written — generator creates them)
14. **Run `DocBlockGenerator`** → creates `Contract/Request/Contract{Mode}Request.php` + Shape files + adds `@property` block to `ContractResource`
15. **`Tests/ContractIntegrationTest.php`**
16. **`Contract/Invoice/InvoiceResource.php`**
17. **5 Invoice controllers**
18. **Run `DocBlockGenerator`** → creates `Contract/Invoice/Request/Invoice{Mode}Request.php` + Shape files + `@property` block on `InvoiceResource`
19. **`Tests/InvoiceIntegrationTest.php`**
20. **`Bootstrap/ApiFactory.php`** — finalize with all controllers registered
21. **`Tests/MiddlewareIntegrationTest.php`**
22. **`Tests/ValidationIntegrationTest.php`**
23. **`composer phpstan`** — run only after all generation is complete; generated Shape classes must exist

---

## Key Implementation Decisions

### Controllers return fixture data

Controllers never hit a database. They return hardcoded fixture data (e.g. a customer with `customer_id = 42`). The point of these tests is the pipeline, not persistence. A controller that returns `null`/wrong type will cause a type error — that is intentional and caught.

Controllers for view/update/delete should return `NotFoundApivalkResponse` for the sentinel IDs defined in the "Sentinel IDs" table above, and a valid fixture response for any other structurally-valid ID. Path params that fail structural validation (wrong type, pattern mismatch) never reach the controller — they return 422 from `RequestValidationMiddleware`.

### No route caching in tests

Pass `new InMemoryCache()` to the Router. Route caching would cause bleed between test runs if a cached route file on disk is stale. The InMemoryCache starts empty each test.

### Superglobal reset

`RequestTrait::resetSuperglobals()` is called in `setUp()` and `tearDown()`. This ensures no test leaks `$_SERVER['HTTP_AUTHORIZATION']` or `$_GET` into the next test.

### Rate limit tests use a dedicated ApiFactory call

Rate limit tests should call `ApiFactory::create(new InMemoryCache(), null, 3, 60)` (max 3 requests per 60-second window) to keep the tests fast without needing 1000 requests.

### `REMOTE_ADDR` for rate limit differentiation

Set `$_SERVER['REMOTE_ADDR']` in each rate limit test to control which "IP" is making requests. Two different IPs should have independent counters.

### Response shape assertions

After `makeRequest()`, tests assert:
- `$response->getStatusCode()` (the primary assertion)
- `$response->toArray()` for specific fields when testing body content (e.g. that `customer_id` is present after create)
- Presence/absence of rate limit headers via `$response->getHeaders()`
- Validation error field names in `$response->toArray()['errors']` for 422 responses

### PHPStan compliance

All new classes must pass PHPStan level 6 without adding to the baseline. Type all method signatures precisely. Use `@param` and `@return` only when inference is insufficient.

### PHP 7.2 compatibility

The library targets PHP 7.2+. Do not use named arguments (`fn: value`), union types, match expressions, nullsafe operator `?->`, or `#[Attribute]` syntax anywhere in the integration test files.

### Router uses ClassLocator, not an array

`Router::__construct(ClassLocator $classLocator, CacheInterface $routerCache)`. The `ClassLocator` scans a directory recursively, derives FQCNs from file paths, and filters to `AbstractApivalkController` subclasses. Point it at the `RealWorld/` directory root. Everything in that directory (Bootstrap, Customer, Contract, etc.) will be scanned — only controllers are registered because the filter checks `is_subclass_of(AbstractApivalkController)`.

### Route cache is built on first dispatch

The Router builds its route index on the first call to `dispatch()` and stores it in the cache. With `InMemoryCache` the index is rebuilt every test, which is correct (no stale routes). Never use a filesystem cache in tests.

### ClassLocator + PSR-4 autoloading

`ClassLocator::find()` calls `class_exists($className)` to trigger autoloading. The derived FQCN must match what the PSR-4 autoloader produces from the file path. Ensure `composer.json` has an `autoload-dev` entry pointing `Tests\\Integration\\RealWorld\\` at the correct directory, and run `composer dump-autoload` after adding it.

### Resource UPDATE makes all body fields optional

`RequestDocumentationFactory::createRequestDocumentation()` clones every body property and calls `setIsRequired(false)` when mode is `MODE_UPDATE`. This means a resource PATCH with an empty body passes validation and reaches the controller. Tests for `missingRequiredField_returns422` are only valid on **non-resource** controllers (Customer, Address), where `getDocumentation()` controls optionality. Resource UPDATE tests should assert that an empty body returns **200**, and that constraints (min, max, range) are still enforced when a field *is* present.

### excludeFromMode() affects runtime documentation

Properties returned by `excludeFromMode(MODE_UPDATE)` are not added to the runtime request documentation, so they are invisible to both `RequestValidationMiddleware` and `AbstractUpdateResourceController::getResource()`. Returning `customer_id` in the update exclusion list prevents it from being overwritten even if the caller sends it in the body.

### Path param auto-merge in Update is name-based

`AbstractUpdateResourceController::getResource()` iterates `$request->path()` and calls `$resource->name = $value` for each param whose name matches a declared resource property. For nested routes like `/contracts/{contract_uuid}/invoices/{invoice_uuid}`, both params are merged — but only if the `InvoiceResource` has properties named `contract_uuid` and `invoice_uuid`. If the names don't match nothing is set and no error is raised.

### getResponseClasses() is OpenAPI-only

It is not read by the router or middleware at runtime. Return the correct classes anyway (for OpenAPI generation and correctness), but runtime test failures are not caused by wrong values here.

### Body population order: php://input → $_POST

`ParameterBagFactory::createBodyBag()` reads `php://input` as JSON first, then merges `$_POST`. In a PHPUnit process `php://input` is empty, so `$_POST` is the effective body source. All test body fields are scalar — this is sufficient. If a future test needs a nested object body, it must write valid JSON to a stream wrapper or refactor the body population.

### Non-resource response classes require getDocumentation()

`AbstractApivalkResponse::getDocumentation()` is abstract — every response class must implement it. For simple responses return an empty `ApivalkResponseDocumentation`. This method is only consumed by the OpenAPI generator; it does not affect runtime behavior or validation.

### `init()` on properties — when to call it and when NOT to

`AbstractProperty::init()` builds the validator chain from the current constraint state (min, max, pattern, etc.). It must be called exactly once, after all setters, and the correct place depends on context:

| Context | Who calls `init()` | What to do |
|---------|-------------------|------------|
| `$doc->addBodyProperty($prop)` | `addBodyProperty()` calls it automatically | Do **not** call `->init()` yourself |
| `$doc->addQueryProperty($prop)` | Same | Do **not** call `->init()` yourself |
| `$doc->addPathProperty($prop)` | Same | Do **not** call `->init()` yourself |
| `Route::pathProperty($prop)` | `buildRuntimeDocumentation()` → `addPathProperty()` calls it | Do **not** call `->init()` yourself |
| Filter factory `StringFilter::equals($prop)` | Filter constructor calls it automatically | Do **not** call `->init()` yourself |
| `AbstractResource::addProperty($prop)` | `RequestDocumentationFactory` later calls `addBodyProperty()` which calls `init()` | Do **not** call `->init()` in `AbstractResource::init()` |

The rule is simple: **never call `->init()` yourself**. Every place the framework accepts a property it handles `init()` internally. Setting constraints after the property has been accepted would be a bug regardless — always chain setters before passing the property in.

**Correct patterns — never call `->init()` yourself:**

```php
// Path property — no init(), buildRuntimeDocumentation handles it
Route::get('/v1/api/customers/{customer_id}')
    ->pathProperty(
        (new IntegerProperty('customer_id', 'Customer ID'))->setMinimumValue(1)
    );

// Filter property — no init(), filter constructor handles it
public function availableFilters(): array
{
    return [
        StringFilter::like(
            (new StringProperty('title', 'Title'))->setMaxLength(255)
        ),
        FloatFilter::greaterThan(
            (new FloatProperty('value', 'Value'))->setMinimumValue(0.01)
        ),
    ];
}

// Body property in getDocumentation() — no init(), addBodyProperty handles it
public static function getDocumentation(): ApivalkRequestDocumentation
{
    $doc = new ApivalkRequestDocumentation();
    $doc->addBodyProperty(
        (new StringProperty('first_name', 'First name'))->setMinLength(1)->setMaxLength(100)
    );
    return $doc;
}

// Resource property in AbstractResource::init() — no init(), addBodyProperty handles it later
protected function init(): void
{
    $this->addProperty(
        (new StringProperty('title', 'Title'))->setMaxLength(255)
    );
}
```

---

## PHPUnit Configuration

### composer.json — autoload-dev

`ClassLocator` derives FQCNs by resolving file paths against a namespace root. Add an `autoload-dev` entry so Composer maps the integration test namespace correctly:

```json
"autoload-dev": {
    "psr-4": {
        "Tests\\Integration\\RealWorld\\": "Tests/Integration/RealWorld/"
    }
}
```

Run `composer dump-autoload` after adding this. Without it `class_exists()` inside `ClassLocator::find()` will fail to load controller classes and the router will discover nothing.

### phpunit.xml

```xml
<testsuite name="Integration">
    <directory>Tests/Integration/RealWorld/Tests</directory>
</testsuite>
```

Run integration tests only:
```bash
vendor/bin/phpunit --testsuite Integration
```

Run a single file:
```bash
vendor/bin/phpunit Tests/Integration/RealWorld/Tests/CustomerIntegrationTest.php
```

Run PHPStan after DocBlockGenerator has written all Shape files:
```bash
composer phpstan
```
