<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\MethodNotAllowedApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Middleware\MiddlewareStack;

class Router extends AbstractRouter
{
    private function getServerRequestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? MethodInterface::METHOD_GET;
    }

    private function getServerRequestUrlPath(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    public function dispatch(MiddlewareStack $middlewareStack): AbstractApivalkResponse
    {
        $requestMethod = $this->getServerRequestMethod();
        $requestUrlPath = $this->getServerRequestUrlPath();

        $routeIndexCache = $this->getRouteIndexCache();
        if ($routeIndexCache === null) {
            return new NotFoundApivalkResponse();
        }

        $matchingRoutes = $this->getMatchingRoutes($routeIndexCache, $requestUrlPath);
        if (\count($matchingRoutes) === 0) {
            return new NotFoundApivalkResponse();
        }

        if (!isset($matchingRoutes[$requestMethod])) {
            return new MethodNotAllowedApivalkResponse();
        }

        $request = $this->buildRequestByRoute(
            $matchingRoutes[$requestMethod]['controllerClass'],
            $matchingRoutes[$requestMethod]['route']
        );

        $controller = $this->buildControllerByClass($matchingRoutes[$requestMethod]['controllerClass']);

        return $middlewareStack->handle($request, $controller);
    }

    /** @return array<int, array{regex: string, method: string, key: string, controllerClass: string}>|null */
    private function getRouteIndexCache(): ?array
    {
        $indexCacheItem = $this->getCache()->get(self::CACHE_INDEX_KEY);
        if (!$indexCacheItem instanceof CacheItem) {
            return null;
        }

        $indexCache = json_decode($indexCacheItem->getValue(), true);
        if (!\is_array($indexCache)) {
            return null;
        }

        return $indexCache;
    }

    /**
     * @param class-string<AbstractApivalkController> $controllerClass
     */
    private function buildControllerByClass(string $controllerClass): AbstractApivalkController
    {
        return $this->getControllerFactory()->create($controllerClass);
    }

    /**
     * @param class-string<AbstractApivalkController> $controllerClass
     */
    private function buildRequestByRoute(string $controllerClass, Route $route): ApivalkRequestInterface
    {
        /** @var class-string<ApivalkRequestInterface> $requestClass */
        $requestClass = $controllerClass::getRequestClass();

        $request = new $requestClass();
        $request->populate($route);

        return $request;
    }

    /**
     * @param array<int, array{regex: string, method: string, key: string, controllerClass: class-string<AbstractApivalkController>}> $indexCache
     *
     * @return array<string, array{route: Route, controllerClass: class-string<AbstractApivalkController>}>
     */
    private function getMatchingRoutes(array $indexCache, string $requestUrlPath): array
    {
        $matchingRoutes = [];

        foreach ($indexCache as $indexEntry) {
            if (preg_match($indexEntry['regex'], $requestUrlPath)) {
                $routeCacheItem = $this->getCache()->get($indexEntry['key']);
                if (!$routeCacheItem instanceof CacheItem) {
                    continue;
                }

                $route = Route::byJson($routeCacheItem->getValue());
                $matchingRoutes[$route->getMethod()->getName()] = [
                    'route' => $route,
                    'controllerClass' => $indexEntry['controllerClass'],
                ];
            }
        }

        return $matchingRoutes;
    }

    /**
     * @return array<int, array{route: Route, controllerClass: string}>
     */
    public function getRoutes(): array
    {
        $routeIndexCache = $this->getRouteIndexCache();
        if ($routeIndexCache === null) {
            return [];
        }

        $routes = [];

        foreach ($routeIndexCache as $indexEntry) {
            $routeCacheItem = $this->getCache()->get($indexEntry['key']);
            if (!$routeCacheItem instanceof CacheItem) {
                continue;
            }

            $routes[] = [
                'route' => Route::byJson($routeCacheItem->getValue()),
                'controllerClass' => $indexEntry['controllerClass']
            ];
        }

        return $routes;
    }
}
