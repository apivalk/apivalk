<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Route;

/**
 * @template TResource of AbstractResource
 * @implements ResourceControllerInterface<TResource>
 */
abstract class AbstractResourceController extends AbstractApivalkController implements ResourceControllerInterface
{
    /**
     * Define the route's URL, method, path parameters, authorization, pagination... based on resource.
     * Tags (and for List: filters and sortings) are injected automatically — do not call them here.
     *
     * This method is called by the framework via getRoute(). Do not call it directly.
     *
     * @internal
     */
    abstract protected static function buildRoute(): Route;

    public static function getRoute(): Route
    {
        return static::buildRoute()->tags(static::getEmptyResource()->tags());
    }

    /**
     * @return class-string<TResource>
     */
    abstract public static function getResourceClass(): string;

    /**
     * @return TResource
     */
    public static function getEmptyResource(): AbstractResource
    {
        /** @var class-string<TResource> $resourceClass */
        $resourceClass = static::getResourceClass();

        return new $resourceClass();
    }
}
