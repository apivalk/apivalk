<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Request\CustomerCreateRequest;

class CreateCustomerController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::post('/v1/api/customers')
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:customers'], ['api:customers:create']))
            ->rateLimit(new IpRateLimit('create-customer', 60, 60));
    }

    public static function getRequestClass(): string
    {
        return CustomerCreateRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [CustomerCreatedResponse::class];
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $data = [
            'customer_id' => 42,
            'first_name'  => $request->body()->has('first_name') ? $request->body()->get('first_name')->getValue() : null,
            'last_name'   => $request->body()->has('last_name') ? $request->body()->get('last_name')->getValue() : null,
            'email'       => $request->body()->has('email') ? $request->body()->get('email')->getValue() : null,
            'phone'       => $request->body()->has('phone') ? $request->body()->get('phone')->getValue() : null,
            'status'      => $request->body()->has('status') ? $request->body()->get('status')->getValue() : null,
            'created_at'  => '2024-01-01T00:00:00Z',
            'updated_at'  => '2024-01-01T00:00:00Z',
        ];

        return new CustomerCreatedResponse($data);
    }
}
