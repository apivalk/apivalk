<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Contract;

use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\Resource\AbstractUpdateResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceUpdatedResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;

class UpdateContractController extends AbstractUpdateResourceController
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const NOT_FOUND_UUID = '00000000-0000-4000-8000-000000000000';

    protected static function buildRoute(): Route
    {
        return Route::patch('/v1/api/contracts/{contract_uuid}')
            ->pathProperty(
                (new StringProperty('contract_uuid', 'Contract UUID v4'))->setPattern(self::UUID_PATTERN)
            )
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:contracts'], ['api:contracts:update']));
    }

    public static function getResourceClass(): string
    {
        return ContractResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $contractUuid = $request->path()->has('contract_uuid')
            ? $request->path()->get('contract_uuid')->getValue()
            : null;

        if ($contractUuid === self::NOT_FOUND_UUID) {
            return new NotFoundApivalkResponse();
        }

        $resource = $this->getResource($request);

        if ($resource->contract_uuid === null) {
            $resource->contract_uuid = $contractUuid ?? '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
        }

        return new ResourceUpdatedResponse($resource);
    }
}
