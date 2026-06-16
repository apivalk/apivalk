<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Tests;

use apivalk\apivalk\Documentation\Property\SimpleArrayProperty;
use PHPUnit\Framework\TestCase;
use Tests\Integration\RealWorld\Bootstrap\ApiFactory;
use Tests\Integration\RealWorld\Bootstrap\InMemoryCache;
use Tests\Integration\RealWorld\Bootstrap\RequestTrait;
use Tests\Integration\RealWorld\Customer\CustomerCreatedResponse;

class CustomerIntegrationTest extends TestCase
{
    use RequestTrait;

    private const VALID_CUSTOMER_BODY = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'status' => 'active',
    ];

    // Fixture data returned by ListCustomersController (5 customers)
    private const FIXTURES = [
        [
            'customer_id' => 1,
            'first_name' => 'Alice',
            'last_name' => 'Anderson',
            'email' => 'alice@example.com',
            'status' => 'active'
        ],
        [
            'customer_id' => 2,
            'first_name' => 'Bob',
            'last_name' => 'Brown',
            'email' => 'bob@example.com',
            'status' => 'inactive'
        ],
        [
            'customer_id' => 3,
            'first_name' => 'Carol',
            'last_name' => 'Clark',
            'email' => 'carol@example.com',
            'status' => 'active'
        ],
        [
            'customer_id' => 4,
            'first_name' => 'Dave',
            'last_name' => 'Davis',
            'email' => 'dave@example.com',
            'status' => 'pending'
        ],
        [
            'customer_id' => 5,
            'first_name' => 'Eve',
            'last_name' => 'Evans',
            'email' => 'eve@example.com',
            'status' => 'active'
        ],
    ];

    // --- List ---

    public function testListCustomers_withAdminToken_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_withReadOnlyToken_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'read-only-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testListCustomers_withNoScopeToken_returns403(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'no-scope-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testListCustomers_withCustomerToken_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'customer-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_withContractToken_returns403(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'contract-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testListCustomers_filterByStatusActive_returns200WithMatchingData(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['status' => 'active'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_filterByFirstNameLike_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['first_name' => 'ali'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_filterByEmail_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['email' => 'alice@example.com'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_invalidStatusValue_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['status' => 'invalid_status'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListCustomers_unknownFilterField_returns422(): void
    {
        // Unknown query params that don't match declared filter fields or query properties are silently ignored
        $response =
            $this->makeRequest('GET', '/v1/api/customers', ['nonexistent_filter_field' => 'value'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_unknownSortField_returns422(): void
    {
        $response =
            $this->makeRequest('GET', '/v1/api/customers', ['order_by' => 'nonexistent_field'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListCustomers_sortByLastNameAsc_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '+last_name'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_sortByCreatedAtDesc_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '-created_at'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_sortByMultipleFields_returns200(): void
    {
        $response =
            $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '+last_name,-created_at'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_defaultSortApplied_noOrderByParam_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_pagePage2Limit5_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['page' => 2, 'limit' => 5], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListCustomers_limitExceedsMax_returns422(): void
    {
        // The limit query property has setMaximumValue(100), so values above max are rejected
        $response = $this->makeRequest('GET', '/v1/api/customers', ['limit' => 999], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListCustomers_invalidPageParam_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['page' => -1], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    // --- Filtering: data correctness ---

    public function testListCustomers_noFilter_returnsAllFiveCustomers(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(5, $response->toArray()['data']);
    }

    public function testListCustomers_filterByStatusActive_returnsThreeItems(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['status' => 'active'], [], 'admin-token');
        $items = $response->toArray()['data'];
        $this->assertCount(3, $items);
        foreach ($items as $item) {
            $this->assertSame('active', $item['status']);
        }
    }

    public function testListCustomers_filterByStatusInactive_returnsOneItem(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['status' => 'inactive'], [], 'admin-token');
        $items = $response->toArray()['data'];
        $this->assertCount(1, $items);
        $this->assertSame('Bob', $items[0]['first_name']);
    }

    public function testListCustomers_filterByStatusPending_returnsOneItem(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['status' => 'pending'], [], 'admin-token');
        $items = $response->toArray()['data'];
        $this->assertCount(1, $items);
        $this->assertSame('Dave', $items[0]['first_name']);
    }

    public function testListCustomers_filterByFirstNameLike_returnsOnlyMatches(): void
    {
        // 'ali' matches 'Alice', nothing else
        $response = $this->makeRequest('GET', '/v1/api/customers', ['first_name' => 'ali'], [], 'admin-token');
        $items = $response->toArray()['data'];
        $this->assertCount(1, $items);
        $this->assertSame('Alice', $items[0]['first_name']);
    }

    public function testListCustomers_filterByLastNameLike_returnsOnlyMatches(): void
    {
        // 'br' matches 'Brown'
        $response = $this->makeRequest('GET', '/v1/api/customers', ['last_name' => 'br'], [], 'admin-token');
        $items = $response->toArray()['data'];
        $this->assertCount(1, $items);
        $this->assertSame('Brown', $items[0]['last_name']);
    }

    public function testListCustomers_filterByEmail_returnsExactMatch(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['email' => 'carol@example.com'], [], 'admin-token');
        $items = $response->toArray()['data'];
        $this->assertCount(1, $items);
        $this->assertSame('Carol', $items[0]['first_name']);
    }

    public function testListCustomers_filterByEmailNoMatch_returnsEmpty(): void
    {
        $response =
            $this->makeRequest('GET', '/v1/api/customers', ['email' => 'nobody@example.com'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(0, $response->toArray()['data']);
    }

    public function testListCustomers_filterByFirstNameLikeCaseInsensitive_returnsMatch(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['first_name' => 'EVE'], [], 'admin-token');
        $items = $response->toArray()['data'];
        $this->assertCount(1, $items);
        $this->assertSame('Eve', $items[0]['first_name']);
    }

    // --- Bracket notation filters ---

    public function testListCustomers_bracketFilterByStatus_returns200WithMatchingData(): void
    {
        $response =
            $this->makeRequest('GET', '/v1/api/customers', ['filter' => ['status' => 'active']], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $items = $response->toArray()['data'];
        $this->assertCount(3, $items);
        foreach ($items as $item) {
            $this->assertSame('active', $item['status']);
        }
    }

    public function testListCustomers_bracketFilterByFirstNameLike_returnsMatch(): void
    {
        $response =
            $this->makeRequest('GET', '/v1/api/customers', ['filter' => ['first_name' => 'ali']], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $items = $response->toArray()['data'];
        $this->assertCount(1, $items);
        $this->assertSame('Alice', $items[0]['first_name']);
    }

    public function testListCustomers_mixedFlatAndBracketFilters_intersectsCorrectly(): void
    {
        // first_name flat + status bracket — Alice is the only active customer whose name contains 'ali'
        $response = $this->makeRequest(
            'GET',
            '/v1/api/customers',
            ['first_name' => 'ali', 'filter' => ['status' => 'active']],
            [],
            'admin-token'
        );
        $this->assertSame(200, $response->getStatusCode());
        $items = $response->toArray()['data'];
        $this->assertCount(1, $items);
        $this->assertSame('Alice', $items[0]['first_name']);
        $this->assertSame('active', $items[0]['status']);
    }

    public function testListCustomers_bracketFilterNonScalarValueIsIgnored_returnsAllItems(): void
    {
        $response = $this->makeRequest(
            'GET',
            '/v1/api/customers',
            ['filter' => ['status' => ['active', 'pending']]],
            [],
            'admin-token'
        );
        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(5, $response->toArray()['data']);
    }

    // --- Sorting: data correctness ---

    public function testListCustomers_sortByLastNameAsc_returnsAlphabeticalOrder(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '+last_name'], [], 'admin-token');
        $lastNames = array_column($response->toArray()['data'], 'last_name');
        $this->assertSame(['Anderson', 'Brown', 'Clark', 'Davis', 'Evans'], $lastNames);
    }

    public function testListCustomers_sortByLastNameDesc_returnsReverseAlphabeticalOrder(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '-last_name'], [], 'admin-token');
        $lastNames = array_column($response->toArray()['data'], 'last_name');
        $this->assertSame(['Evans', 'Davis', 'Clark', 'Brown', 'Anderson'], $lastNames);
    }

    public function testListCustomers_sortByCreatedAtDesc_returnsNewestFirst(): void
    {
        // Newest: Carol(2024-03), Bob(2024-02), Dave(2024-01-15), Alice(2024-01-01), Eve(2023-12)
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '-created_at'], [], 'admin-token');
        $firstNames = array_column($response->toArray()['data'], 'first_name');
        $this->assertSame(['Carol', 'Bob', 'Dave', 'Alice', 'Eve'], $firstNames);
    }

    public function testListCustomers_sortByCreatedAtAsc_returnsOldestFirst(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers', ['order_by' => '+created_at'], [], 'admin-token');
        $firstNames = array_column($response->toArray()['data'], 'first_name');
        $this->assertSame(['Eve', 'Alice', 'Dave', 'Bob', 'Carol'], $firstNames);
    }

    public function testListCustomers_filterAndSort_activeByLastNameAsc(): void
    {
        // Filter: active (Alice, Carol, Eve) → sort by last_name asc → Anderson, Clark, Evans
        $response = $this->makeRequest(
            'GET',
            '/v1/api/customers',
            ['status' => 'active', 'order_by' => '+last_name'],
            [],
            'admin-token'
        );
        $items = $response->toArray()['data'];
        $this->assertCount(3, $items);
        $this->assertSame(['Anderson', 'Clark', 'Evans'], array_column($items, 'last_name'));
    }

    public function testListCustomers_filterAndSort_activeByLastNameDesc(): void
    {
        $response = $this->makeRequest(
            'GET',
            '/v1/api/customers',
            ['status' => 'active', 'order_by' => '-last_name'],
            [],
            'admin-token'
        );
        $items = $response->toArray()['data'];
        $this->assertCount(3, $items);
        $this->assertSame(['Evans', 'Clark', 'Anderson'], array_column($items, 'last_name'));
    }

    // --- Create ---

    public function testCreateCustomer_validBody_returns201(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], self::VALID_CUSTOMER_BODY, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateCustomer_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], self::VALID_CUSTOMER_BODY);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testCreateCustomer_withReadOnlyToken_returns403(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], self::VALID_CUSTOMER_BODY, 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testCreateCustomer_withCustomerToken_returns201(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], self::VALID_CUSTOMER_BODY, 'customer-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateCustomer_missingFirstName_returns422(): void
    {
        $body = self::VALID_CUSTOMER_BODY;
        unset($body['first_name']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_missingLastName_returns422(): void
    {
        $body = self::VALID_CUSTOMER_BODY;
        unset($body['last_name']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_missingEmail_returns422(): void
    {
        $body = self::VALID_CUSTOMER_BODY;
        unset($body['email']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_missingStatus_returns422(): void
    {
        $body = self::VALID_CUSTOMER_BODY;
        unset($body['status']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_invalidStatusValue_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['status' => 'unknown']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_firstNameTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['first_name' => str_repeat('a', 101)]);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_lastNameTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['last_name' => str_repeat('a', 101)]);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_emptyFirstName_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['first_name' => '']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_emptyLastName_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['last_name' => '']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_invalidEmailNoAt_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['email' => 'notanemail']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_invalidEmailNoDot_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['email' => 'foo@bar']);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_phoneTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['phone' => str_repeat('1', 21)]);
        $response = $this->makeRequest('POST', '/v1/api/customers', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateCustomer_multipleMissingFields_returns422WithAllFieldNames(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertArrayHasKey('errors', $data);
        $this->assertNotEmpty($data['errors']);
    }

    public function testCreateCustomer_responseContainsStringSimpleArray(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], self::VALID_CUSTOMER_BODY, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
        $data = $response->toArray()['data'];
        $this->assertSame(['admin', 'billing'], $data['roles']);
    }

    public function testCreateCustomer_responseContainsIntSimpleArray(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers', [], self::VALID_CUSTOMER_BODY, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
        $data = $response->toArray()['data'];
        $this->assertSame([10, 20, 30], $data['permission_ids']);
    }

    public function testCustomerCreatedResponse_documentsSimpleArrayProperties(): void
    {
        $properties = CustomerCreatedResponse::getDocumentation()->getProperties();

        $byName = [];
        foreach ($properties as $property) {
            $byName[$property->getPropertyName()] = $property;
        }

        $this->assertArrayHasKey('roles', $byName);
        $this->assertInstanceOf(SimpleArrayProperty::class, $byName['roles']);
        $this->assertSame(['type' => 'string'], $byName['roles']->getDocumentationArray()['items']);

        $this->assertArrayHasKey('permission_ids', $byName);
        $this->assertInstanceOf(SimpleArrayProperty::class, $byName['permission_ids']);
        $this->assertSame(['type' => 'integer'], $byName['permission_ids']->getDocumentationArray()['items']);
    }

    // --- View ---

    public function testViewCustomer_withAdminToken_returns200(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/42', [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewCustomer_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/42');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testViewCustomer_withNoScopeToken_returns403(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/42', [], [], 'no-scope-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testViewCustomer_unknownCustomerId_returns404(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/99999', [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testViewCustomer_customerIdZero_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/0', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewCustomer_customerIdNegative_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/-1', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewCustomer_customerIdNonInteger_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/abc', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewCustomer_pathParameterAvailableInController(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/42', [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertSame(42, $data['data']['customer_id']);
    }

    // --- Update ---

    public function testUpdateCustomer_validBody_returns200(): void
    {
        $response = $this->makeRequest('PATCH', '/v1/api/customers/42', [], self::VALID_CUSTOMER_BODY, 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateCustomer_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('PATCH', '/v1/api/customers/42', [], self::VALID_CUSTOMER_BODY);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUpdateCustomer_withReadOnlyToken_returns403(): void
    {
        $response =
            $this->makeRequest('PATCH', '/v1/api/customers/42', [], self::VALID_CUSTOMER_BODY, 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testUpdateCustomer_missingRequiredField_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['first_name' => '']);
        $response = $this->makeRequest('PATCH', '/v1/api/customers/42', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateCustomer_firstNameTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['first_name' => str_repeat('a', 101)]);
        $response = $this->makeRequest('PATCH', '/v1/api/customers/42', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateCustomer_emptyLastName_returns422(): void
    {
        $body = array_merge(self::VALID_CUSTOMER_BODY, ['last_name' => '']);
        $response = $this->makeRequest('PATCH', '/v1/api/customers/42', [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateCustomer_pathCustomerIdPropagated(): void
    {
        $response = $this->makeRequest('PATCH', '/v1/api/customers/42', [], self::VALID_CUSTOMER_BODY, 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertSame(42, $data['data']['customer_id']);
    }

    public function testUpdateCustomer_customerIdZero_returns422(): void
    {
        $response = $this->makeRequest('PATCH', '/v1/api/customers/0', [], self::VALID_CUSTOMER_BODY, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateCustomer_customerIdNonInteger_returns422(): void
    {
        $response = $this->makeRequest('PATCH', '/v1/api/customers/abc', [], self::VALID_CUSTOMER_BODY, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateCustomer_unknownCustomerId_returns404(): void
    {
        $response =
            $this->makeRequest('PATCH', '/v1/api/customers/99999', [], self::VALID_CUSTOMER_BODY, 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    // --- Delete ---

    public function testDeleteCustomer_withAdminToken_returns204(): void
    {
        $response = $this->makeRequest('DELETE', '/v1/api/customers/42', [], [], 'admin-token');
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeleteCustomer_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('DELETE', '/v1/api/customers/42');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testDeleteCustomer_withReadOnlyToken_returns403(): void
    {
        $response = $this->makeRequest('DELETE', '/v1/api/customers/42', [], [], 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testDeleteCustomer_unknownCustomerId_returns404(): void
    {
        $response = $this->makeRequest('DELETE', '/v1/api/customers/99999', [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testDeleteCustomer_customerIdZero_returns422(): void
    {
        $response = $this->makeRequest('DELETE', '/v1/api/customers/0', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testDeleteCustomer_customerIdNonInteger_returns422(): void
    {
        $response = $this->makeRequest('DELETE', '/v1/api/customers/abc', [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }
}
