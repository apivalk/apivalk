<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Contract\Invoice;

use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\Resource\AbstractUpdateResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceUpdatedResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;

class UpdateInvoiceController extends AbstractUpdateResourceController
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const NOT_FOUND_UUID = '00000000-0000-4000-8000-000000000001';

    protected static function buildRoute(): Route
    {
        return Route::patch('/v1/api/contracts/{contract_uuid}/invoices/{invoice_uuid}')
            ->pathProperty(
                (new StringProperty('contract_uuid', 'Contract UUID v4'))->setPattern(self::UUID_PATTERN)
            )
            ->pathProperty(
                (new StringProperty('invoice_uuid', 'Invoice UUID v4'))->setPattern(self::UUID_PATTERN)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:contracts:invoices'], ['api:contracts:invoices:update']));
    }

    public static function getResourceClass(): string
    {
        return InvoiceResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $invoiceUuid = $request->path()->has('invoice_uuid')
            ? $request->path()->get('invoice_uuid')->getValue()
            : null;

        if ($invoiceUuid === self::NOT_FOUND_UUID) {
            return new NotFoundApivalkResponse();
        }

        $resource = $this->getResource($request);

        return new ResourceUpdatedResponse($resource);
    }
}
