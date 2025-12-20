<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Cache;

use apivalk\apivalk\Router\Route;

class RouterCacheCollection
{
    /** @var RouterCacheEntry[] */
    private $routeCacheEntries = [];

    public function addRouteCacheEntry(Route $route, string $controllerClass): void
    {
        $routeUrl = $route->getUrl();

        $escaped = str_replace(['/', '.'], ['\/', '\.'], $routeUrl);
        $regexPattern = preg_replace('#\{[a-zA-Z0-9_]+\}#', '([a-zA-Z0-9_-]+)', $escaped);
        $regexPattern = '#^' . $regexPattern . '$#';

        $this->routeCacheEntries[] = new RouterCacheEntry($route, $regexPattern, $controllerClass);
    }

    public function getRouteCacheEntries(): array
    {
        return $this->routeCacheEntries;
    }

    public static function byJson(string $json): self
    {
        $routeCache = new self();
        $jsonArray = json_decode($json, true);

        if (!\is_array($jsonArray)) {
            throw new \InvalidArgumentException('Invalid JSON provided for route cache');
        }

        foreach ($jsonArray as $route) {
            $routeCache->addRouteCacheEntry(Route::byJson(json_encode($route['route'])), $route['controllerClass']);
        }

        return $routeCache;
    }
}
