<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer;

use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\DeletedApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Request\CustomerDeleteRequest;

class DeleteCustomerController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::delete('/v1/api/customers/{customer_id}')
            ->pathProperty(
                (new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:customers'], ['api:customers:delete']));
    }

    public static function getRequestClass(): string
    {
        return CustomerDeleteRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [DeletedApivalkResponse::class, NotFoundApivalkResponse::class];
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $customerId = $request->path()->has('customer_id')
            ? (int) $request->path()->get('customer_id')->getValue()
            : null;

        if ($customerId === 99999) {
            return new NotFoundApivalkResponse();
        }

        return new DeletedApivalkResponse();
    }
}
