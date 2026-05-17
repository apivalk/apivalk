<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address;

use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Address\Request\AddressViewRequest;

class ViewAddressController extends AbstractApivalkController
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const NOT_FOUND_UUID = '00000000-0000-4000-8000-000000000000';

    public static function getRoute(): Route
    {
        return Route::get('/v1/api/customers/{customer_id}/addresses/{address_uuid}')
            ->pathProperty(
                (new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1)
            )
            ->pathProperty(
                (new StringProperty('address_uuid', 'Address UUID v4'))->setPattern(self::UUID_PATTERN)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:customers:address'], ['api:customers:address:read']));
    }

    public static function getRequestClass(): string
    {
        return AddressViewRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [AddressViewResponse::class, NotFoundApivalkResponse::class];
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $customerId = $request->path()->has('customer_id')
            ? (int) $request->path()->get('customer_id')->getValue()
            : null;

        $addressUuid = $request->path()->has('address_uuid')
            ? $request->path()->get('address_uuid')->getValue()
            : null;

        if ($addressUuid === self::NOT_FOUND_UUID) {
            return new NotFoundApivalkResponse();
        }

        return new AddressViewResponse([
            'address_uuid' => $addressUuid ?? '550e8400-e29b-41d4-a716-446655440000',
            'customer_id'  => $customerId ?? 42,
            'street'       => '123 Main St',
            'city'         => 'Springfield',
            'zip'          => '12345',
            'country'      => 'US',
            'type'         => 'billing',
            'is_primary'   => true,
            'created_at'   => '2024-01-01T00:00:00Z',
        ]);
    }
}
