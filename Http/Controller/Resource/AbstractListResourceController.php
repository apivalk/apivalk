<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Http\Response\BadRequestApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceListResponse;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Route;

/**
 * @template TResource of AbstractResource
 * @extends AbstractResourceController<TResource>
 */
abstract class AbstractListResourceController extends AbstractResourceController
{
    public static function getRoute(): Route
    {
        $resource = static::getEmptyResource();

        $route = static::buildRoute()
            ->tags($resource->tags())
            ->filtering($resource->availableFilters())
            ->sorting($resource->availableSortings());

        static::assertRouteMethod(
            $route,
            [GetMethod::class],
            'List resource controllers document filters, sortings and pagination as query parameters.'
        );

        return $route;
    }

    public static function getRequestClass(): string
    {
        return ResourceRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [
            ResourceListResponse::class,
            BadRequestApivalkResponse::class,
            ForbiddenApivalkResponse::class,
        ];
    }
}
