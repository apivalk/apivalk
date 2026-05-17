<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Contract\Invoice;

use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\Resource\AbstractViewResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceViewResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;

class ViewInvoiceController extends AbstractViewResourceController
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const NOT_FOUND_UUID = '00000000-0000-4000-8000-000000000001';

    protected static function buildRoute(): Route
    {
        return Route::get('/v1/api/contracts/{contract_uuid}/invoices/{invoice_uuid}')
            ->pathProperty(
                (new StringProperty('contract_uuid', 'Contract UUID v4'))->setPattern(self::UUID_PATTERN)
            )
            ->pathProperty(
                (new StringProperty('invoice_uuid', 'Invoice UUID v4'))->setPattern(self::UUID_PATTERN)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:contracts:invoices'], ['api:contracts:invoices:read']));
    }

    public static function getResourceClass(): string
    {
        return InvoiceResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $contractUuid = $request->path()->has('contract_uuid')
            ? $request->path()->get('contract_uuid')->getValue()
            : null;

        $invoiceUuid = $request->path()->has('invoice_uuid')
            ? $request->path()->get('invoice_uuid')->getValue()
            : null;

        if ($invoiceUuid === self::NOT_FOUND_UUID) {
            return new NotFoundApivalkResponse();
        }

        $resource = new InvoiceResource();
        $resource->invoice_uuid = $invoiceUuid ?? 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $resource->contract_uuid = $contractUuid ?? '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
        $resource->invoice_number = 'INV-2024-0001';
        $resource->amount = 500.00;
        $resource->tax_rate = 19.0;
        $resource->total_amount = 595.00;
        $resource->status = 'draft';
        $resource->due_date = '2024-03-01';
        $resource->created_at = '2024-01-01T00:00:00Z';

        return new ResourceViewResponse($resource);
    }
}
