<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Tests;

use PHPUnit\Framework\TestCase;
use Tests\Integration\RealWorld\Bootstrap\RequestTrait;

class ValidationIntegrationTest extends TestCase
{
    use RequestTrait;

    private const CONTRACT_UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const INVOICE_UUID  = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

    // --- Path parameter validation ---

    public function testPath_integerPathParam_zero_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/0', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testPath_integerPathParam_negative_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/-1', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testPath_integerPathParam_nonNumeric_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/abc', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testPath_stringPathParam_invalidUuidV4Pattern_returns422(): void
    {
        // Only UUID v4 (third block starts with '4', fourth block starts with 8/9/a/b) is accepted
        $response = $this->makeRequest('GET', '/v1/api/contracts/' . self::CONTRACT_UUID . '/invoices/invalid-uuid', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testPath_stringPathParam_uuidV1_returns422(): void
    {
        // UUID v1 (third block starts with '1') fails the v4 pattern
        $response = $this->makeRequest('GET', '/v1/api/contracts/6ba7b810-9dad-11d1-80b4-00c04fd430c8', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testPath_validUuidV4_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/contracts/' . self::CONTRACT_UUID, [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Query parameter validation ---

    public function testQuery_paginationLimitBelowMin_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['limit' => 0], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testQuery_paginationLimitAtMax_returns200(): void
    {
        // max limit for customers is 100; exactly at max is valid
        $response = $this->makeRequest('GET', '/v1/api/customers', ['limit' => 100], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testQuery_paginationLimitAboveMax_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['limit' => 101], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testQuery_paginationPageBelowMin_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['page' => 0], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testQuery_paginationPageValidValue_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['page' => 1], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testQuery_sortOrderByValidAscending_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '+last_name'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testQuery_sortOrderByValidDescending_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '-created_at'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testQuery_sortOrderByUnknownField_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => 'unknown_field'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testQuery_filterEnumInvalidValue_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['status' => 'not_a_status'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testQuery_filterEnumValidValue_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['status' => 'active'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testQuery_unknownQueryParamIgnored_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['totally_unknown' => 'value'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Body validation ---

    public function testBody_stringTooShort_returns422(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'first_name' => '',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'status'     => 'active',
        ], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testBody_stringTooLong_returns422(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'first_name' => str_repeat('a', 101),
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'status'     => 'active',
        ], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testBody_stringAtMaxLength_returns201(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'first_name' => str_repeat('a', 100),
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'status'     => 'active',
        ], 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testBody_emailPatternMismatch_returns422(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'not-valid',
            'status'     => 'active',
        ], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testBody_floatBelowMinimum_returns422(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/contracts', [], [
            'customer_id' => 42,
            'title'       => 'Test',
            'value'       => 0.0,
            'status'      => 'active',
            'start_date'  => '2024-01-01',
        ], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testBody_floatAtMinimum_returns201(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/contracts', [], [
            'customer_id' => 42,
            'title'       => 'Test',
            'value'       => 0.01,
            'status'      => 'active',
            'start_date'  => '2024-01-01',
        ], 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testBody_integerBelowMinimum_returns422(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/contracts', [], [
            'customer_id' => 0,
            'title'       => 'Test',
            'value'       => 100.0,
            'status'      => 'active',
            'start_date'  => '2024-01-01',
        ], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testBody_multipleErrors_returns422WithMultipleErrorEntries(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertArrayHasKey('errors', $data);
        $this->assertGreaterThan(1, count($data['errors']));
    }

    public function testBody_optionalFieldAbsent_returns201(): void
    {
        // phone is optional in customer create
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'email'      => 'jane@example.com',
            'status'     => 'active',
        ], 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testBody_optionalFieldPresent_returns201(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'email'      => 'jane@example.com',
            'status'     => 'active',
            'phone'      => '+1234567890',
        ], 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    // --- Update mode: resource controllers make all fields optional ---

    public function testBody_resourceUpdateWithEmptyBody_returns200(): void
    {
        // AbstractUpdateResourceController marks all resource fields optional in UPDATE mode
        $response = $this->makeRequest('PATCH', '/v1/api/contracts/' . self::CONTRACT_UUID, [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testBody_resourceUpdateWithPartialBody_returns200(): void
    {
        $response = $this->makeRequest('PATCH', '/v1/api/contracts/' . self::CONTRACT_UUID, [], ['title' => 'Updated'], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testBody_resourceUpdateWithInvalidFieldValue_returns422(): void
    {
        $response = $this->makeRequest('PATCH', '/v1/api/contracts/' . self::CONTRACT_UUID, [], ['status' => 'invalid'], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testBody_nonResourceUpdateRequiresExplicitFields_returns422WithEmptyBody(): void
    {
        // Non-resource controllers (CustomerUpdateRequest) define required fields explicitly → empty body fails
        $response = $this->makeRequest('PATCH', '/v1/api/customers/42', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    // --- Nested resource path validation ---

    public function testBody_nestedResource_invalidContractUuidInPath_returns422(): void
    {
        $response = $this->makeRequest(
            'POST',
            '/v1/api/contracts/not-a-uuid/invoices',
            [],
            [
                'amount'   => 100.0,
                'tax_rate' => 19.0,
                'status'   => 'draft',
                'due_date' => '2024-03-01',
            ],
            'admin-token'
        );
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testBody_nestedResource_validPathAndBody_returns201(): void
    {
        $response = $this->makeRequest(
            'POST',
            '/v1/api/contracts/' . self::CONTRACT_UUID . '/invoices',
            [],
            [
                'amount'   => 100.0,
                'tax_rate' => 19.0,
                'status'   => 'draft',
                'due_date' => '2024-03-01',
            ],
            'admin-token'
        );
        $this->assertSame(201, $response->getStatusCode());
    }
}
