<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactoryInterface;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactory;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Util\ClassLocator;

abstract class AbstractRouter
{
    /** @var ApivalkControllerFactoryInterface */
    private $controllerFactory;
    /** @var ClassLocator */
    private $classLocator;
    /** @var CacheInterface */
    private $cache;

    public const CACHE_INDEX_KEY = 'apivalk.router.index';
    public const CACHE_ROUTE_KEY = 'apivalk.router.route';

    abstract public function dispatch(MiddlewareStack $middlewareStack): AbstractApivalkResponse;

    /**
     * @return array<int, array{route: Route, controllerClass: string}>
     */
    abstract public function getRoutes(): array;

    public function __construct(
        ClassLocator $classLocator,
        CacheInterface $routerCache,
        ?ApivalkControllerFactoryInterface $controllerFactory = null
    ) {
        $this->classLocator = $classLocator;

        if ($controllerFactory === null) {
            $controllerFactory = new ApivalkControllerFactory();
        }

        $this->controllerFactory = $controllerFactory;
        $this->cache = $routerCache;

        (new RouteCacheFactory($this))->build();
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function getClassLocator(): ClassLocator
    {
        return $this->classLocator;
    }

    public function getControllerFactory(): ApivalkControllerFactoryInterface
    {
        return $this->controllerFactory;
    }
}
