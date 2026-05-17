<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Tests;

use PHPUnit\Framework\TestCase;
use Tests\Integration\RealWorld\Bootstrap\RequestTrait;

class InvoiceIntegrationTest extends TestCase
{
    use RequestTrait;

    private const CONTRACT_UUID       = '550e8400-e29b-41d4-a716-446655440000';
    private const VALID_INVOICE_UUID  = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
    private const NOT_FOUND_UUID      = '00000000-0000-4000-8000-000000000001';
    private const INVALID_UUID        = 'not-a-uuid';

    private const VALID_INVOICE_BODY = [
        'amount'   => 500.00,
        'tax_rate' => 19.0,
        'status'   => 'draft',
        'due_date' => '2024-03-01',
    ];

    private function listUrl(string $contractUuid = self::CONTRACT_UUID): string
    {
        return '/v1/api/contracts/' . $contractUuid . '/invoices';
    }

    private function itemUrl(string $contractUuid, string $invoiceUuid): string
    {
        return '/v1/api/contracts/' . $contractUuid . '/invoices/' . $invoiceUuid;
    }

    // --- List ---

    public function testListInvoices_withAdminToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_withContractToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'contract-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_withReadOnlyToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'read-only-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl());
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testListInvoices_withNoScopeToken_returns403(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'no-scope-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testListInvoices_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testListInvoices_invalidContractUuid_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(self::INVALID_UUID), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListInvoices_filterByStatusDraft_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['status' => 'draft'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_filterByStatusPaid_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['status' => 'paid'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_filterByInvalidStatus_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['status' => 'unknown_status'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListInvoices_filterByAmountGreaterThan_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['amount' => '100.00'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_filterByDueDateGreaterThan_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['due_date' => '2024-01-01'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_filterByPaidAt_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['paid_at' => '2024-01-01T00:00:00Z'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_unknownFilterField_returns200(): void
    {
        // Unknown query params are silently ignored
        $response = $this->makeRequest('GET', $this->listUrl(), ['nonexistent_field' => 'value'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_sortByDueDateAsc_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['order_by' => '+due_date'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_sortByCreatedAtDesc_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['order_by' => '-created_at'], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_unknownSortField_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['order_by' => 'nonexistent_field'], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testListInvoices_offsetPaginationWithLimitAndOffset_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->listUrl(), ['limit' => 25, 'offset' => 0], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListInvoices_limitExceedsMax_returns422(): void
    {
        // max limit is 100; exceeding it returns 422
        $response = $this->makeRequest('GET', $this->listUrl(), ['limit' => 999], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    // --- Create ---

    public function testCreateInvoice_validBody_returns201(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_INVOICE_BODY, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateInvoice_withContractToken_returns201(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_INVOICE_BODY, 'contract-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateInvoice_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_INVOICE_BODY);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testCreateInvoice_withReadOnlyToken_returns403(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_INVOICE_BODY, 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testCreateInvoice_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], self::VALID_INVOICE_BODY, 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testCreateInvoice_invalidContractUuid_returns422(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(self::INVALID_UUID), [], self::VALID_INVOICE_BODY, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_missingAmount_returns422(): void
    {
        $body = self::VALID_INVOICE_BODY;
        unset($body['amount']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_missingTaxRate_returns422(): void
    {
        $body = self::VALID_INVOICE_BODY;
        unset($body['tax_rate']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_missingStatus_returns422(): void
    {
        $body = self::VALID_INVOICE_BODY;
        unset($body['status']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_missingDueDate_returns422(): void
    {
        $body = self::VALID_INVOICE_BODY;
        unset($body['due_date']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_invalidStatus_returns422(): void
    {
        $body = array_merge(self::VALID_INVOICE_BODY, ['status' => 'unknown']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_amountBelowMinimum_returns422(): void
    {
        $body = array_merge(self::VALID_INVOICE_BODY, ['amount' => 0]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_taxRateAboveMaximum_returns422(): void
    {
        $body = array_merge(self::VALID_INVOICE_BODY, ['tax_rate' => 101.0]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_taxRateBelowMinimum_returns422(): void
    {
        $body = array_merge(self::VALID_INVOICE_BODY, ['tax_rate' => -1.0]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateInvoice_withOptionalPaidAt_returns201(): void
    {
        $body = array_merge(self::VALID_INVOICE_BODY, ['paid_at' => '2024-03-15T10:00:00Z']);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateInvoice_emptyBody_returns422(): void
    {
        $response = $this->makeRequest('POST', $this->listUrl(), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertArrayHasKey('errors', $data);
        $this->assertNotEmpty($data['errors']);
    }

    public function testCreateInvoice_totalAmountExcludedFromCreateResponse(): void
    {
        // total_amount is excluded from CREATE mode per InvoiceResource::excludeFromMode
        $body = array_merge(self::VALID_INVOICE_BODY, ['amount' => 100.0, 'tax_rate' => 19.0]);
        $response = $this->makeRequest('POST', $this->listUrl(), [], $body, 'admin-token');
        $this->assertSame(201, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertArrayNotHasKey('total_amount', $data['data']);
    }

    // --- View ---

    public function testViewInvoice_withAdminToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewInvoice_withContractToken_returns200(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'contract-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewInvoice_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID));
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testViewInvoice_withNoScopeToken_returns403(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'no-scope-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testViewInvoice_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testViewInvoice_unknownInvoiceUuid_returns404(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::CONTRACT_UUID, self::NOT_FOUND_UUID), [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testViewInvoice_invalidContractUuid_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::INVALID_UUID, self::VALID_INVOICE_UUID), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewInvoice_invalidInvoiceUuid_returns422(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::CONTRACT_UUID, self::INVALID_UUID), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testViewInvoice_invoiceDataAvailableInResponse(): void
    {
        $response = $this->makeRequest('GET', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('invoice_uuid', $data['data']);
        $this->assertArrayHasKey('amount', $data['data']);
        $this->assertArrayHasKey('status', $data['data']);
    }

    // --- Update ---

    public function testUpdateInvoice_validBody_returns200(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], ['status' => 'sent'], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateInvoice_emptyBody_returns200(): void
    {
        // In UPDATE mode, all resource fields are optional
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'admin-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateInvoice_withContractToken_returns200(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], ['status' => 'paid'], 'contract-token');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateInvoice_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], ['status' => 'sent']);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUpdateInvoice_withReadOnlyToken_returns403(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], ['status' => 'sent'], 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testUpdateInvoice_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], ['status' => 'sent'], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testUpdateInvoice_unknownInvoiceUuid_returns404(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::NOT_FOUND_UUID), [], ['status' => 'sent'], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testUpdateInvoice_invalidContractUuid_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::INVALID_UUID, self::VALID_INVOICE_UUID), [], ['status' => 'sent'], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateInvoice_invalidInvoiceUuid_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::INVALID_UUID), [], ['status' => 'sent'], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateInvoice_invalidStatus_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], ['status' => 'invalid'], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateInvoice_amountBelowMinimum_returns422(): void
    {
        $response = $this->makeRequest('PATCH', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], ['amount' => 0], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    // --- Delete ---

    public function testDeleteInvoice_withAdminToken_returns204(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'admin-token');
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeleteInvoice_withContractToken_returns204(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'contract-token');
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeleteInvoice_withoutToken_returns401(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID));
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testDeleteInvoice_withReadOnlyToken_returns403(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'read-only-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testDeleteInvoice_withCustomerToken_returns403(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::CONTRACT_UUID, self::VALID_INVOICE_UUID), [], [], 'customer-token');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testDeleteInvoice_unknownInvoiceUuid_returns404(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::CONTRACT_UUID, self::NOT_FOUND_UUID), [], [], 'admin-token');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testDeleteInvoice_invalidContractUuid_returns422(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::INVALID_UUID, self::VALID_INVOICE_UUID), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testDeleteInvoice_invalidInvoiceUuid_returns422(): void
    {
        $response = $this->makeRequest('DELETE', $this->itemUrl(self::CONTRACT_UUID, self::INVALID_UUID), [], [], 'admin-token');
        $this->assertSame(422, $response->getStatusCode());
    }
}
