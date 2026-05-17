<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Contract;

use apivalk\apivalk\Http\Controller\Resource\AbstractCreateResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceCreatedResponse;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;

class CreateContractController extends AbstractCreateResourceController
{
    protected static function buildRoute(): Route
    {
        return Route::post('/v1/api/contracts')
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:contracts'], ['api:contracts:create']))
            ->rateLimit(new IpRateLimit('create-contract', 60, 60));
    }

    public static function getResourceClass(): string
    {
        return ContractResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $resource = $this->getResource($request);

        return new ResourceCreatedResponse($resource);
    }
}
