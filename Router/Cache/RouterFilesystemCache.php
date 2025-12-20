<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Cache;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Util\ClassLocator;

class RouterFilesystemCache implements RouterCacheInterface
{
    /** @var ClassLocator */
    private $apiClassLocator;
    /** @var int */
    private $cacheLifetime;
    /** @var RouterCacheCollection|null */
    private $routerCacheCollection = null;
    /** @var string */
    private $cacheDirectory;

    public function __construct(string $cacheDirectory, ClassLocator $apiClassLocator, int $cacheLifetime = 600)
    {
        $this->apiClassLocator = $apiClassLocator;
        $this->cacheDirectory = $cacheDirectory;
        $this->cacheLifetime = $cacheLifetime;
    }

    public function getCacheFilePath(): string
    {
        return \sprintf('%s/apivalk-php-router-cache.tmp', $this->cacheDirectory);
    }

    public function getRouterCacheCollection(): RouterCacheCollection
    {
        $this->checkRouterCacheCollection();

        return $this->routerCacheCollection;
    }

    private function checkRouterCacheCollection(): void
    {
        $cacheFile = $this->getCacheFilePath();

        if (!file_exists($cacheFile)
            || (time() - filemtime($cacheFile)) > $this->cacheLifetime) {
            $this->buildRouteCache();
        }

        if ($this->routerCacheCollection === null) {
            $cacheFileContent = file_get_contents($cacheFile);
            if (!$cacheFileContent) {
                throw new \RuntimeException(\sprintf('Could not read cache file: %s', $cacheFile));
            }

            $this->routerCacheCollection = RouterCacheCollection::byJson($cacheFileContent);
        }
    }

    private function buildRouteCache(): void
    {
        $classesInNamespace = $this->apiClassLocator->findClasses();

        $routerCacheCollection = new RouterCacheCollection();

        /** @var class-string<AbstractApivalkController> $className */
        foreach ($classesInNamespace as $class) {
            $className = $class['className'];

            if (!is_subclass_of($className, AbstractApivalkController::class)) {
                continue;
            }

            $route = $className::getRoute();
            $routerCacheCollection->addRouteCacheEntry($route, $className);
        }

        $cacheFile = $this->getCacheFilePath();
        $cacheDir = dirname($cacheFile);

        if (!is_dir($cacheDir)
            && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
            throw new \RuntimeException(\sprintf('Could not create cache directory: %s', $cacheDir));
        }

        file_put_contents($this->getCacheFilePath(), json_encode($routerCacheCollection->getRouteCacheEntries()));
        $this->routerCacheCollection = $routerCacheCollection;
    }
}
