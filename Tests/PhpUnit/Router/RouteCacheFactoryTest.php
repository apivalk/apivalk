<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\RouteCacheFactory;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Util\ClassLocator;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

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
        $cache->expects($this->once())->method('clear');

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
