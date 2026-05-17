<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address;

use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Address\Request\AddressCreateRequest;

class CreateAddressController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::post('/v1/api/customers/{customer_id}/addresses')
            ->pathProperty(
                (new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:customers:address'], ['api:customers:address:create']))
            ->rateLimit(new IpRateLimit('create-address', 60, 60));
    }

    public static function getRequestClass(): string
    {
        return AddressCreateRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [AddressCreatedResponse::class];
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $customerId = $request->path()->has('customer_id')
            ? (int) $request->path()->get('customer_id')->getValue()
            : null;

        $data = [
            'address_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'customer_id'  => $customerId ?? 42,
            'street'       => $request->body()->has('street') ? $request->body()->get('street')->getValue() : null,
            'city'         => $request->body()->has('city') ? $request->body()->get('city')->getValue() : null,
            'zip'          => $request->body()->has('zip') ? $request->body()->get('zip')->getValue() : null,
            'country'      => $request->body()->has('country') ? $request->body()->get('country')->getValue() : null,
            'type'         => $request->body()->has('type') ? $request->body()->get('type')->getValue() : null,
            'is_primary'   => $request->body()->has('is_primary') ? $request->body()->get('is_primary')->getValue() : false,
            'created_at'   => '2024-01-01T00:00:00Z',
        ];

        return new AddressCreatedResponse($data);
    }
}
