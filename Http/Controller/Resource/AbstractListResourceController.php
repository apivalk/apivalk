<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Http\Response\BadRequestApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceListResponse;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;

/**
 * @template TResource of AbstractResource
 * @extends AbstractResourceController<TResource>
 */
abstract class AbstractListResourceController extends AbstractResourceController
{
    public static function getDescription(): string
    {
        return \sprintf('List %s', self::getEmptyResource()->getPluralName());
    }

    public static function pagination(): ?Pagination
    {
        return null;
    }

    protected static function configureRoute(Route $route): void
    {
        $route->filtering(self::getEmptyResource()->availableFilters());
        $route->sorting(self::getEmptyResource()->availableSortings());

        $pagination = static::pagination();
        if ($pagination !== null) {
            $route->pagination($pagination);
        }
    }

    public static function getMode(): string
    {
        return AbstractResource::MODE_LIST;
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
