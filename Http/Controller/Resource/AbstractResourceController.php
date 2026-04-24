<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;

/**
 * @template TResource of AbstractResource
 * @implements ResourceControllerInterface<TResource>
 */
abstract class AbstractResourceController extends AbstractApivalkController implements ResourceControllerInterface
{
    /**
     * @return class-string<TResource>
     */
    abstract public static function getResourceClass(): string;

    abstract public static function getDescription(): string;

    /**
     * @return TResource
     */
    public static function getEmptyResource(): AbstractResource
    {
        /** @var class-string<TResource> $resourceClass */
        $resourceClass = static::getResourceClass();

        return new $resourceClass();
    }

    public static function getRoute(): Route
    {
        $resource = self::getEmptyResource();
        $route = Route::resource($resource, static::getMode());

        $route->description(static::getDescription());
        $route->tags(static::getEmptyResource()->tags());

        $rateLimit = static::rateLimit();
        if ($rateLimit !== null) {
            $route->rateLimit($rateLimit);
        }

        $authorization = static::routeAuthorization();
        if ($authorization !== null) {
            $route->routeAuthorization($authorization);
        }

        static::configureRoute($route);

        return $route;
    }

    /**
     * Hook for mode-specific route configuration. Default is a no-op; modes that need extra
     * wiring (e.g. list filters/sortings/pagination) override this.
     */
    protected static function configureRoute(Route $route): void
    {
    }

    public static function rateLimit(): ?RateLimitInterface
    {
        return null;
    }

    public static function routeAuthorization(): ?RouteAuthorization
    {
        return null;
    }
}
