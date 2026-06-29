<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\RouteCacheFactory;
use apivalk\apivalk\Util\ClassLocator;
use PHPUnit\Framework\TestCase;

abstract class AbstractIntermediateStubController extends AbstractApivalkController
{
}

class RouteCacheFactoryTest extends TestCase
{
    public function testBuildCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $classLocator = $this->createMock(ClassLocator::class);
        
        // Return null for initial check to force build
        $cache->method('get')->with(AbstractRouter::CACHE_INDEX_KEY)->willReturn(null);
        
        $controllerClass = get_class(new class extends AbstractApivalkController {
            public static function getRoute(): Route { return new Route('/test', new GetMethod()); }
            public static function getRequestClass(): string { return ''; }
            public static function getResponseClasses(): array { return []; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse {
                return $this->createMock(AbstractApivalkResponse::class);
            }
        });

        $classLocator->method('find')->willReturn([
            ['className' => $controllerClass, 'path' => 'path/to/controller.php']
        ]);

        $router = $this->getMockBuilder(AbstractRouter::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $router->method('getCache')->willReturn($cache);
        $router->method('getClassLocator')->willReturn($classLocator);

        // Expect cache sets
        $cache->expects($this->atLeastOnce())->method('set');
        // clear() must never run: it is non-atomic and wipes route files a concurrent process relies on.
        $cache->expects($this->never())->method('clear');

        $factory = new RouteCacheFactory($router);
        $factory->build();
    }

    public function testBuildCacheGivesRouteEntriesLongerLifetimeThanIndex(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $classLocator = $this->createMock(ClassLocator::class);

        $cache->method('get')->with(AbstractRouter::CACHE_INDEX_KEY)->willReturn(null);
        $cache->method('getDefaultCacheLifetime')->willReturn(600);

        $controllerClass = get_class(new class extends AbstractApivalkController {
            public static function getRoute(): Route { return new Route('/test', new GetMethod()); }
            public static function getRequestClass(): string { return ''; }
            public static function getResponseClasses(): array { return []; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse {
                return $this->createMock(AbstractApivalkResponse::class);
            }
        });

        $classLocator->method('find')->willReturn([
            ['className' => $controllerClass, 'path' => 'path/to/controller.php']
        ]);

        $router = $this->getMockBuilder(AbstractRouter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $router->method('getCache')->willReturn($cache);
        $router->method('getClassLocator')->willReturn($classLocator);

        $ttlByKey = [];
        $cache->method('set')->willReturnCallback(function (CacheItem $item) use (&$ttlByKey) {
            $ttlByKey[$item->getKey()] = $item->getTtl();
            return true;
        });

        $factory = new RouteCacheFactory($router);
        $factory->build();

        $indexTtl = $ttlByKey[AbstractRouter::CACHE_INDEX_KEY];
        unset($ttlByKey[AbstractRouter::CACHE_INDEX_KEY]);

        $this->assertNotEmpty($ttlByKey, 'expected at least one route entry to be cached');
        foreach ($ttlByKey as $key => $routeTtl) {
            $this->assertGreaterThan(
                $indexTtl,
                $routeTtl,
                \sprintf('route entry "%s" must outlive the index so an expired index rebuilds before routes vanish', $key)
            );
        }
    }

    public function testBuildCacheSkipsAbstractControllerSubclasses(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $classLocator = $this->createMock(ClassLocator::class);

        $cache->method('get')->with(AbstractRouter::CACHE_INDEX_KEY)->willReturn(null);

        $classLocator->method('find')->willReturn([
            ['className' => AbstractIntermediateStubController::class, 'path' => 'path/to/abstract.php'],
        ]);

        $router = $this->getMockBuilder(AbstractRouter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $router->method('getCache')->willReturn($cache);
        $router->method('getClassLocator')->willReturn($classLocator);

        $cache->expects($this->never())->method('clear');
        // Only the index entry is written; no per-route cache entry for the abstract class.
        $cache->expects($this->once())->method('set');

        $factory = new RouteCacheFactory($router);
        $factory->build();
    }

    public function testBuildCacheSkipsIfAlreadyExists(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cacheItem = new CacheItem(AbstractRouter::CACHE_INDEX_KEY, '[]');
        $cache->method('get')->with(AbstractRouter::CACHE_INDEX_KEY)->willReturn($cacheItem);
        
        $router = $this->getMockBuilder(AbstractRouter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $router->method('getCache')->willReturn($cache);

        $cache->expects($this->never())->method('clear');
        $cache->expects($this->never())->method('set');

        $factory = new RouteCacheFactory($router);
        $factory->build();
    }
}
