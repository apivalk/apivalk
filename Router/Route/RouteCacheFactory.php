<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route;

use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Router\AbstractRouter;

class RouteCacheFactory
{
    /**
     * Route entries live longer than the index so that an expired index always triggers a
     * rebuild while its route files are still present, avoiding a window where the index
     * references already-expired route files.
     */
    private const ROUTE_CACHE_LIFETIME_BUFFER = 60;

    private AbstractRouter $abstractRouter;

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

        $cacheIndex = [];

        $classesInNamespace = $this->abstractRouter->getClassLocator()->find();

        foreach ($classesInNamespace as $class) {
            /** @var class-string<AbstractApivalkController> $className */
            $className = $class['className'];

            if (!is_subclass_of($className, AbstractApivalkController::class)) {
                continue;
            }

            if ((new \ReflectionClass($className))->isAbstract()) {
                continue;
            }

            // AbstractApivalkController cannot declare __invoke() abstractly without pinning
            // every controller to the interface parameter, so the contract is checked here.
            if (!method_exists($className, '__invoke')) {
                throw new \LogicException(\sprintf(
                    'Controller "%s" has no __invoke() method.',
                    $className
                ));
            }

            $route = $className::getRoute();

            $routeCacheKey = $this->getRouteCacheKey($route);

            $cache->set(
                new CacheItem(
                    $routeCacheKey,
                    json_encode(RouteJsonSerializer::serialize($route)),
                    $cache->getDefaultCacheLifetime() + self::ROUTE_CACHE_LIFETIME_BUFFER
                )
            );

            $cacheIndex[] = [
                'regex' => RouteRegexFactory::build($route),
                'method' => $route->getMethod()->getName(),
                'key' => $routeCacheKey,
                'controllerClass' => $className,
            ];

            if ($route->isQueryEnabled()) {
                // Same route, same controller, reachable a second time as RFC 10008 QUERY.
                $cacheIndex[] = [
                    'regex' => RouteRegexFactory::build($route),
                    'method' => MethodInterface::METHOD_QUERY,
                    'key' => $routeCacheKey,
                    'controllerClass' => $className,
                ];
            }
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
