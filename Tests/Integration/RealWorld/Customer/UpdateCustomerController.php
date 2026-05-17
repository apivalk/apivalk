<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer;

use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Request\CustomerUpdateRequest;

class UpdateCustomerController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::patch('/v1/api/customers/{customer_id}')
            ->pathProperty(
                (new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:customers'], ['api:customers:update']));
    }

    public static function getRequestClass(): string
    {
        return CustomerUpdateRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [CustomerUpdatedResponse::class, NotFoundApivalkResponse::class];
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $customerId = $request->path()->has('customer_id')
            ? (int) $request->path()->get('customer_id')->getValue()
            : null;

        if ($customerId === 99999) {
            return new NotFoundApivalkResponse();
        }

        return new CustomerUpdatedResponse([
            'customer_id' => $customerId ?? 42,
            'first_name'  => $request->body()->has('first_name') ? $request->body()->get('first_name')->getValue() : 'John',
            'last_name'   => $request->body()->has('last_name') ? $request->body()->get('last_name')->getValue() : 'Doe',
            'email'       => $request->body()->has('email') ? $request->body()->get('email')->getValue() : 'john@example.com',
            'status'      => $request->body()->has('status') ? $request->body()->get('status')->getValue() : 'active',
            'updated_at'  => '2024-06-01T00:00:00Z',
        ]);
    }
}
