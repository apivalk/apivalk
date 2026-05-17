<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Tests;

use PHPUnit\Framework\TestCase;
use Tests\Integration\RealWorld\Bootstrap\RequestTrait;

class ContractIntegrationTest extends TestCase
{
    use RequestTrait;

    private const VALID_UUID        = '550e8400-e29b-41d4-a716-446655440000';
    private const NOT_FOUND_UUID    = '00000000-0000-4000-8000-000000000000';
    private const INVALID_UUID      = 'not-a-uuid';

    private const VALID_CONTRACT_BODY = [
        'customer_id' => 42,
        'title'       => 'Test Contract',
        'value'       => 1000.00,
        'status'      => 'active',
        'start_date'  => '2024-01-01',
    ];

    private function listUrl(): string
    {
        return '/v1/api/contracts';
    }

    private function itemUrl(string $uuid): string
    {
        return '/v1/api/contracts/' . $uuid;
    }

    // --- List ---

    public function testListContracts_withAdminToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_withContractToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'contract-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_withReadOnlyToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'read-only-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl());
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testListContracts_withNoScopeToken_returns403(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'no-scope-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testListContracts_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testListContracts_filterByStatusActive_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['status' => 'active'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_filterByStatusDraft_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['status' => 'draft'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_filterByInvalidStatus_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['status' => 'unknown_status'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListContracts_filterByCustomerId_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['customer_id' => 42], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_filterByTitleLike_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['title' => 'Sample'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_filterByValueGreaterThan_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['value' => '500.00'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_filterByStartDateGreaterThan_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['start_date' => '2024-01-01'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_unknownFilterField_returns200(): void
    {
        // Unknown query params that don't match declared filter fields are silently ignored
        $response = $this->makeRequest('GET', $this->listUrl(), ['nonexistent_field' => 'value'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_sortByTitleAsc_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['order_by' => '+title'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_sortByStartDateDesc_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['order_by' => '-start_date'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_unknownSortField_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['order_by' => 'nonexistent_field'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListContracts_cursorPaginationWithLimit_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['limit' => 10], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListContracts_limitExceedsMax_returns422(): void
    {
        // max limit is 50 for cursor pagination; exceeding it returns 422
        $response = $this->makeRequest('GET', $this->listUrl(), ['limit' => 999], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListContracts_cursorParam_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['cursor' => 'some-cursor-value', 'limit' => 10], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Create ---

    public function testCreateContract_validBody_returns201(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_CONTRACT_BODY, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateContract_withContractToken_returns201(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_CONTRACT_BODY, 'contract-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateContract_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_CONTRACT_BODY);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testCreateContract_withReadOnlyToken_returns403(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_CONTRACT_BODY, 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testCreateContract_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_CONTRACT_BODY, 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testCreateContract_missingTitle_returns422(): void
    {
        $body = self::VALID_CONTRACT_BODY;
        unset($body['title']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_missingValue_returns422(): void
    {
        $body = self::VALID_CONTRACT_BODY;
        unset($body['value']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_missingStatus_returns422(): void
    {
        $body = self::VALID_CONTRACT_BODY;
        unset($body['status']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_missingStartDate_returns422(): void
    {
        $body = self::VALID_CONTRACT_BODY;
        unset($body['start_date']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_missingCustomerId_returns422(): void
    {
        $body = self::VALID_CONTRACT_BODY;
        unset($body['customer_id']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_invalidStatus_returns422(): void
    {
        $body = array_merge(self::VALID_CONTRACT_BODY, ['status' => 'unknown']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_valueBelowMinimum_returns422(): void
    {
        $body = array_merge(self::VALID_CONTRACT_BODY, ['value' => 0]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_titleTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_CONTRACT_BODY, ['title' => str_repeat('a', 256)]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_emptyTitle_returns422(): void
    {
        $body = array_merge(self::VALID_CONTRACT_BODY, ['title' => '']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_withOptionalFields_returns201(): void
    {
        $body = array_merge(self::VALID_CONTRACT_BODY, [
            'currency' => 'EUR',
            'end_date' => '2025-12-31',
            'notes'    => 'Some notes',
        ]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateContract_currencyTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_CONTRACT_BODY, ['currency' => 'EURO']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_notesTooLong_returns422(): void
    {
        $body = array_merge(self::VALID_CONTRACT_BODY, ['notes' => str_repeat('a', 5001)]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateContract_emptyBody_returns422(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertArrayHasKey('errors', $data);
        $this->assertNotEmpty($data['errors']);
    }

    // --- View ---

    public function testViewContract_withAdminToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::VALID_UUID), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewContract_withContractToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::VALID_UUID), [], [], 'contract-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewContract_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::VALID_UUID));
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testViewContract_withNoScopeToken_returns403(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::VALID_UUID), [], [], 'no-scope-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testViewContract_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::VALID_UUID), [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testViewContract_unknownUuid_returns404(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::NOT_FOUND_UUID), [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testViewContract_invalidUuid_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::INVALID_UUID), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewContract_uuidNotMatchingPattern_returns422(): void
    {
        // Non-v4 UUID (version 1) should fail the UUID v4 pattern validation
        $response = $this->makeRequest('GET', $this->itemUrl('6ba7b810-9dad-11d1-80b4-00c04fd430c8'), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewContract_contractUuidAvailableInResponse(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::VALID_UUID), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('contract_uuid', $data['data']);
    }

    // --- Update ---

    public function testUpdateContract_validBody_returns200(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], ['title' => 'Updated'], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateContract_emptyBody_returns200(): void
    {
        // In UPDATE mode, resource controllers make all fields optional, so empty body is valid
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateContract_withContractToken_returns200(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], ['title' => 'Updated'], 'contract-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateContract_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], ['title' => 'Updated']);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUpdateContract_withReadOnlyToken_returns403(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], ['title' => 'Updated'], 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testUpdateContract_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], ['title' => 'Updated'], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testUpdateContract_unknownUuid_returns404(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::NOT_FOUND_UUID), [], ['title' => 'Updated'], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testUpdateContract_invalidUuid_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::INVALID_UUID), [], ['title' => 'Updated'], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateContract_invalidStatusValue_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], ['status' => 'invalid'], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateContract_valueBelowMinimum_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], ['value' => 0], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateContract_titleTooLong_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::VALID_UUID), [], ['title' => str_repeat('a', 256)], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    // --- Delete ---

    public function testDeleteContract_withAdminToken_returns204(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::VALID_UUID), [], [], 'admin-token');
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeleteContract_withContractToken_returns204(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::VALID_UUID), [], [], 'contract-token');
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeleteContract_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::VALID_UUID));
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testDeleteContract_withReadOnlyToken_returns403(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::VALID_UUID), [], [], 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testDeleteContract_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::VALID_UUID), [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testDeleteContract_unknownUuid_returns404(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::NOT_FOUND_UUID), [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testDeleteContract_invalidUuid_returns422(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::INVALID_UUID), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }
}
