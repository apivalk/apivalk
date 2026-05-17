<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address;

use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationResponse;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Filter\BooleanFilter;
use apivalk\apivalk\Router\Route\Filter\EnumFilter;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Address\Request\AddressListRequest;

class ListAddressesController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::get('/v1/api/customers/{customer_id}/addresses')
            ->pathProperty(
                (new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:customers:address'], ['api:customers:address:read']))
            ->filtering([
                StringFilter::like(new StringProperty('city', 'City')),
                StringFilter::equals(new StringProperty('country', 'Country')),
                EnumFilter::equals(new EnumProperty('type', 'Type', ['billing', 'shipping', 'both'])),
                BooleanFilter::equals(new BooleanProperty('is_primary', 'Is primary', false)),
            ])
            ->sorting([
                Sort::asc('city'),
                Sort::asc('country'),
                Sort::asc('type'),
                Sort::desc('created_at'),
            ])
            ->pagination(Pagination::page()->setMaxLimit(50))
            ->rateLimit(new IpRateLimit('list-addresses', 60, 60));
    }

    public static function getRequestClass(): string
    {
        return AddressListRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [AddressListResponse::class];
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $customerId = $request->path()->has('customer_id')
            ? (int) $request->path()->get('customer_id')->getValue()
            : null;

        $fixture = [
            [
                'address_uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'customer_id'  => $customerId ?? 42,
                'street'       => '123 Main St',
                'city'         => 'Springfield',
                'zip'          => '12345',
                'country'      => 'US',
                'type'         => 'billing',
                'is_primary'   => true,
                'created_at'   => '2024-01-01T00:00:00Z',
            ],
        ];

        $response = new AddressListResponse($fixture);
        $response->setPaginationResponse(new PagePaginationResponse(1, 25, false, 1));

        return $response;
    }
}
