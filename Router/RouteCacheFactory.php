<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;

class RouteCacheFactory
{
    /** @var AbstractRouter */
    private $abstractRouter;

    public function __construct(AbstractRouter $abstractRouter)
    {
        $this->abstractRouter = $abstractRouter;
    }

    public function build(): void
    {
        $cache = $this->abstractRouter->getCache();

        $cacheIndex = $cache->get(AbstractRouter::CACHE_INDEX_KEY);
        if ($cacheIndex instanceof CacheItem) {
            return;
        }

        $cache->clear();

        $cacheIndex = [];

        $classesInNamespace = $this->abstractRouter->getClassLocator()->find();

        foreach ($classesInNamespace as $class) {
            /** @var class-string<AbstractApivalkController> $className */
            $className = $class['className'];

            if (!is_subclass_of($className, AbstractApivalkController::class)) {
                continue;
            }

            $route = $className::getRoute();

            $routeCacheKey = $this->getRouteCacheKey($route);

            $cache->set(
                new CacheItem(
                    $routeCacheKey,
                    json_encode(RouteJsonSerializer::serialize($route)),
                    $cache->getDefaultCacheLifetime()
                )
            );

            $cacheIndex[] = [
                'regex' => RouteRegexFactory::build($route),
                'method' => $route->getMethod()->getName(),
                'key' => $routeCacheKey,
                'controllerClass' => $className,
            ];
        }

        $cache->set(
            new CacheItem(
                AbstractRouter::CACHE_INDEX_KEY,
                json_encode($cacheIndex),
                $cache->getDefaultCacheLifetime()
            )
        );
    }

    private function getRouteCacheKey(Route $route): string
    {
        return \sprintf('%s.%s_%s', AbstractRouter::CACHE_ROUTE_KEY, $route->getMethod()->getName(), $route->getUrl());
    }
}
