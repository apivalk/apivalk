<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address;

use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\DeletedApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Address\Request\AddressDeleteRequest;

class DeleteAddressController extends AbstractApivalkController
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const NOT_FOUND_UUID = '00000000-0000-4000-8000-000000000000';

    public static function getRoute(): Route
    {
        return Route::delete('/v1/api/customers/{customer_id}/addresses/{address_uuid}')
            ->pathProperty(
                (new IntegerProperty('customer_id', 'Customer integer ID'))->setMinimumValue(1)
            )
            ->pathProperty(
                (new StringProperty('address_uuid', 'Address UUID v4'))->setPattern(self::UUID_PATTERN)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:customers:address'], ['api:customers:address:delete']));
    }

    public static function getRequestClass(): string
    {
        return AddressDeleteRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [DeletedApivalkResponse::class, NotFoundApivalkResponse::class];
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $addressUuid = $request->path()->has('address_uuid')
            ? $request->path()->get('address_uuid')->getValue()
            : null;

        if ($addressUuid === self::NOT_FOUND_UUID) {
            return new NotFoundApivalkResponse();
        }

        return new DeletedApivalkResponse();
    }
}
