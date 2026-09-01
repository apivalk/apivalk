<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\MethodInterface;
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
     * Each resource controller derives its request and response documentation from the HTTP method
     * it is meant for, so a mismatching buildRoute() would publish a contract the controller does not
     * implement. Subclasses call this from getRoute() to reject one early.
     *
     * @param array<int, class-string<MethodInterface>> $allowedMethodClasses
     */
    protected static function assertRouteMethod(Route $route, array $allowedMethodClasses, string $reason): void
    {
        foreach ($allowedMethodClasses as $methodClass) {
            if ($route->getMethod() instanceof $methodClass) {
                return;
            }
        }

        $allowedNames = [];
        foreach ($allowedMethodClasses as $methodClass) {
            $allowedNames[] = (new $methodClass())->getName();
        }

        throw new \InvalidArgumentException(\sprintf(
            'Controller "%s" must return a %s route from buildRoute(), got "%s" for "%s". %s',
            static::class,
            \implode(' or ', $allowedNames),
            $route->getMethod()->getName(),
            $route->getUrl(),
            $reason
        ));
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
