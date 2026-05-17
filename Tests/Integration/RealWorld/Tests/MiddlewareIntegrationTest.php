<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Tests;

use PHPUnit\Framework\TestCase;
use Tests\Integration\RealWorld\Bootstrap\RequestTrait;

class MiddlewareIntegrationTest extends TestCase
{
    use RequestTrait;

    // --- AuthenticationMiddleware ---

    public function testAuth_noToken_returns401(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAuth_unknownToken_returns401(): void
    {
        // TestAuthenticator returns null for unrecognised tokens → GuestAuthIdentity → unauthenticated
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'completely-unknown-token-xyz');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAuth_validToken_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAuth_runsBeforeValidation_noTokenWithInvalidBody_returns401(): void
    {
        // Auth check must fire before request body validation; missing token takes precedence over bad body
        $response = $this->makeRequest('POST', '/v1/api/customers', [], []);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAuth_runsBeforeValidation_noTokenWithInvalidQueryParam_returns401(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['status' => 'totally_invalid']);
        $this->assertSame(401, $response->getStatusCode());
    }

    // --- SecurityMiddleware (authorization) ---

    public function testSecurity_missingScope_returns403(): void
    {
        // no-scope-token has no scopes at all
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'no-scope-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testSecurity_wrongScope_returns403(): void
    {
        // customer-token lacks api:contracts scope
        $response = $this->makeRequest('GET', '/v1/api/contracts', [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testSecurity_hasReadScopeButWritePermissionMissing_returns403(): void
    {
        // read-only-token has no create permission
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'status'     => 'active',
        ], 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testSecurity_runsBeforeValidation_wrongScopeWithInvalidBody_returns403(): void
    {
        // Security check must fire before body validation; wrong scope takes precedence over bad body
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [], 'no-scope-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    // --- RequestValidationMiddleware ---

    public function testValidation_missingRequiredField_returns422WithErrors(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'last_name' => 'Doe',
            'email'     => 'john@example.com',
            'status'    => 'active',
        ], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertArrayHasKey('errors', $data);
        $this->assertNotEmpty($data['errors']);
    }

    public function testValidation_invalidEnumValue_returns422(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'status'     => 'nonexistent',
        ], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testValidation_invalidQueryParamType_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['page' => -5], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testValidation_unknownRoute_returns404(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/nonexistent-route', [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testValidation_wrongHttpMethod_returns405(): void
    {
        // PATCH on a list-only route resolves to no matching controller → 405 Method Not Allowed
        $response = $this->makeRequest('PATCH', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(405, $response->getStatusCode());
    }

    // --- RateLimitMiddleware ---

    public function testRateLimit_withinLimit_returns200(): void
    {
        // The list-customers route has a limit of 60 per 60s; a single request is well within it
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRateLimit_exhaustLimit_returns429(): void
    {
        // Each test uses a fresh InMemoryCache; list-customers allows 60 requests before blocking.
        // The 60th request gets remaining=0 → 429.
        $lastResponse = null;
        for ($i = 0; $i < 60; $i++) {
            $lastResponse = $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token');
        }
        $this->assertSame(429, $lastResponse->getStatusCode());
    }

    public function testRateLimit_differentPublicIpHasSeparateCounter(): void
    {
        // IpResolver rejects private/loopback IPs (returns null for them).
        // Use distinct public IPs so each gets its own rate-limit counter.
        for ($i = 0; $i < 60; $i++) {
            $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token', '8.8.8.8');
        }

        // A different public IP has a fresh counter
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token', '1.1.1.1');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRateLimit_requestAfterExhaustion_returns429(): void
    {
        // Exhaust the limit, then verify subsequent requests are also blocked
        for ($i = 0; $i < 60; $i++) {
            $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token');
        }
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(429, $response->getStatusCode());
    }

    public function testRateLimit_routeWithoutRateLimit_always200(): void
    {
        // View/Update/Delete customer routes have no rate limit — never blocked
        for ($i = 0; $i < 5; $i++) {
            $response = $this->makeRequest('GET', '/v1/api/customers/42', [], [], 'admin-token');
            $this->assertSame(200, $response->getStatusCode());
        }
    }
}
