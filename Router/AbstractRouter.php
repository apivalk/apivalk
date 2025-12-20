<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Http\Controller\ApivalkControllerFactoryInterface;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactory;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Router\Cache\RouterCacheInterface;

abstract class AbstractRouter
{
    /** @var RouterCacheInterface */
    private $routerCache;

    /** @var ApivalkControllerFactoryInterface */
    private $controllerFactory;

    abstract public function dispatch(MiddlewareStack $middlewareStack): AbstractApivalkResponse;

    public function __construct(RouterCacheInterface $routerCache, ?ApivalkControllerFactoryInterface $controllerFactory = null)
    {
        if ($controllerFactory === null) {
            $controllerFactory = new ApivalkControllerFactory();
        }

        $this->controllerFactory = $controllerFactory;
        $this->routerCache = $routerCache;
    }

    public function getRouterCache(): RouterCacheInterface
    {
        return $this->routerCache;
    }

    public function getControllerFactory(): ApivalkControllerFactoryInterface
    {
        return $this->controllerFactory;
    }
}
