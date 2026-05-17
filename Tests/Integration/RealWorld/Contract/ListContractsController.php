<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Contract;

use apivalk\apivalk\Http\Controller\Resource\AbstractListResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Pagination\CursorPaginationResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceListResponse;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;

class ListContractsController extends AbstractListResourceController
{
    protected static function buildRoute(): Route
    {
        return Route::get('/v1/api/contracts')
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:contracts'], ['api:contracts:read']))
            ->pagination(Pagination::cursor()->setMaxLimit(50))
            ->rateLimit(new IpRateLimit('list-contracts', 60, 60));
    }

    public static function getResourceClass(): string
    {
        return ContractResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $resource = new ContractResource();
        $resource->contract_uuid = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
        $resource->customer_id = 42;
        $resource->title = 'Sample Contract';
        $resource->value = 1000.00;
        $resource->currency = 'EUR';
        $resource->status = 'active';
        $resource->start_date = '2024-01-01';

        $pagination = new CursorPaginationResponse(10, 'initial', 'next-cursor', false);

        return new ResourceListResponse([$resource], $pagination);
    }
}
