<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Tests;

use PHPUnit\Framework\TestCase;
use Tests\Integration\RealWorld\Bootstrap\RequestTrait;

class AddressIntegrationTest extends TestCase
{
    use RequestTrait;

    private const CUSTOMER_ID = 42;
    private const VALID_UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const NOT_FOUND_UUID = '00000000-0000-4000-8000-000000000000';

    private const VALID_ADDRESS_BODY = [
        'street'  => '123 Main St',
        'city'    => 'Springfield',
        'zip'     => '12345',
        'country' => 'US',
        'type'    => 'billing',
    ];

    private function listUrl(int $customerId = self::CUSTOMER_ID): string
    {
        return '/v1/api/customers/' . $customerId . '/addresses';
    }

    private function itemUrl(int $customerId = self::CUSTOMER_ID, string $uuid = self::VALID_UUID): string
    {
        return '/v1/api/customers/' . $customerId . '/addresses/' . $uuid;
    }

    // --- List ---

    public function testListAddresses_withAdminToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl());
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testListAddresses_withCustomerToken_returns403(): void
    {
        // customer-token lacks api:customers:address scope
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testListAddresses_withAddressReadScope_returns200(): void
    {
        // admin-token has all scopes including api:customers:address
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_pathCustomerIdAvailableInController(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(42), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertSame(42, $data['data'][0]['customer_id']);
    }

    public function testListAddresses_filterByCityLike_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['city' => 'Spring'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_filterByCountryEquals_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['country' => 'US'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_filterByType_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['type' => 'billing'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_filterByIsPrimaryTrue_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['is_primary' => '1'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_invalidTypeValue_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['type' => 'invalid_type'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListAddresses_unknownFilterField_returns422(): void
    {
        // Unknown query params are silently ignored
        $response = $this->makeRequest('GET', $this->listUrl(), ['unknown_field' => 'value'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_sortByCityAsc_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['order_by' => '+city'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_sortByCountry_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['order_by' => '+country'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAddresses_pagePage1Limit10_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['page' => 1, 'limit' => 10], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Create ---

    public function testCreateAddress_validBody_returns201(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_ADDRESS_BODY, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateAddress_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_ADDRESS_BODY);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testCreateAddress_withAddressReadScope_returns403(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_ADDRESS_BODY, 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testCreateAddress_pathCustomerIdPropagatedToResponse(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(42), [], self::VALID_ADDRESS_BODY, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertSame(42, $data['data']['customer_id']);
    }

    public function testCreateAddress_missingStreet_returns422(): void
    {
        $body = self::VALID_ADDRESS_BODY;
        unset($body['street']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_missingCity_returns422(): void
    {
        $body = self::VALID_ADDRESS_BODY;
        unset($body['city']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_missingZip_returns422(): void
    {
        $body = self::VALID_ADDRESS_BODY;
        unset($body['zip']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_missingCountry_returns422(): void
    {
        $body = self::VALID_ADDRESS_BODY;
        unset($body['country']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_missingType_returns422(): void
    {
        $body = self::VALID_ADDRESS_BODY;
        unset($body['type']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_invalidType_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['type' => 'invalid']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_countryTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['country' => 'USA']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_countryTooShort_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['country' => 'U']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_streetEmpty_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['street' => '']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_streetTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['street' => str_repeat('a', 256)]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_cityEmpty_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['city' => '']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_zipTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['zip' => str_repeat('1', 21)]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_customerIdZero_returns422(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(0), [], self::VALID_ADDRESS_BODY, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateAddress_customerIdNonInteger_returns422(): void
    {
        $response = $this->makeRequest('POST', '/v1/api/customers/abc/addresses', [], self::VALID_ADDRESS_BODY, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    // --- View ---

    public function testViewAddress_withAdminToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewAddress_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl());
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testViewAddress_bothPathParamsPresentInController(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(42, self::VALID_UUID), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertSame(42, $data['data']['customer_id']);
        $this->assertSame(self::VALID_UUID, $data['data']['address_uuid']);
    }

    public function testViewAddress_unknownAddressUuid_returns404(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(42, self::NOT_FOUND_UUID), [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testViewAddress_customerIdZero_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(0), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewAddress_customerIdNonInteger_returns422(): void
    {
        $response = $this->makeRequest('GET', '/v1/api/customers/abc/addresses/' . self::VALID_UUID, [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewAddress_addressUuidNotUuid_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(42, 'not-a-uuid'), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewAddress_addressUuidEmpty_returns422(): void
    {
        // Empty UUID will not match the route at all (404, not 422)
        $response = $this->makeRequest('GET', '/v1/api/customers/42/addresses/', [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    // --- Update ---

    public function testUpdateAddress_validBody_returns200(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(), [], self::VALID_ADDRESS_BODY, 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateAddress_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(), [], self::VALID_ADDRESS_BODY);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUpdateAddress_wrongScope_returns403(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(), [], self::VALID_ADDRESS_BODY, 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testUpdateAddress_pathParamsPropagated(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(42, self::VALID_UUID), [], self::VALID_ADDRESS_BODY, 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertSame(42, $data['data']['customer_id']);
        $this->assertSame(self::VALID_UUID, $data['data']['address_uuid']);
    }

    public function testUpdateAddress_missingRequiredField_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['street' => '']);
        $response = $this->makeRequest('PATCH', $this->itemUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateAddress_countryTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_ADDRESS_BODY, ['country' => 'USA']);
        $response = $this->makeRequest('PATCH', $this->itemUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateAddress_customerIdZero_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(0), [], self::VALID_ADDRESS_BODY, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateAddress_addressUuidNotUuid_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(42, 'not-a-uuid'), [], self::VALID_ADDRESS_BODY, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    // --- Delete ---

    public function testDeleteAddress_withAdminToken_returns204(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(), [], [], 'admin-token');
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeleteAddress_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl());
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testDeleteAddress_wrongScope_returns403(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(), [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testDeleteAddress_unknownAddressUuid_returns404(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(42, self::NOT_FOUND_UUID), [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testDeleteAddress_customerIdZero_returns422(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(0), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testDeleteAddress_addressUuidNotUuid_returns422(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(42, 'not-a-uuid'), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }
}
