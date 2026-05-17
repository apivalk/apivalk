<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Contract\Invoice;

use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\Resource\AbstractCreateResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceCreatedResponse;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;

class CreateInvoiceController extends AbstractCreateResourceController
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    protected static function buildRoute(): Route
    {
        return Route::post('/v1/api/contracts/{contract_uuid}/invoices')
            ->pathProperty(
                (new StringProperty('contract_uuid', 'Contract UUID v4'))->setPattern(self::UUID_PATTERN)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:contracts:invoices'], ['api:contracts:invoices:create']))
            ->rateLimit(new IpRateLimit('create-invoice', 60, 60));
    }

    public static function getResourceClass(): string
    {
        return InvoiceResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $resource = $this->getResource($request);

        $amount = $resource->amount !== null ? (float) $resource->amount : 0.0;
        $taxRate = $resource->tax_rate !== null ? (float) $resource->tax_rate : 0.0;
        $resource->total_amount = round($amount * (1 + $taxRate / 100), 2);

        $contractUuid = $request->path()->has('contract_uuid')
            ? $request->path()->get('contract_uuid')->getValue()
            : '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

        $resource->contract_uuid = $contractUuid;
        $resource->invoice_uuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $resource->invoice_number = 'INV-2024-0001';
        $resource->created_at = '2024-01-01T00:00:00Z';

        return new ResourceCreatedResponse($resource);
    }
}
