<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Router\Cache\RouterCacheInterface;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactoryInterface;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class AbstractRouterTest extends TestCase
{
    public function testGetters(): void
    {
        $cache = $this->createMock(RouterCacheInterface::class);
        $factory = $this->createMock(ApivalkControllerFactoryInterface::class);
        
        $router = new class($cache, $factory) extends AbstractRouter {
            public function dispatch(MiddlewareStack $middlewareStack): AbstractApivalkResponse {
                return $this->createMock(AbstractApivalkResponse::class);
            }
        };
        
        $this->assertSame($cache, $router->getRouterCache());
        $this->assertSame($factory, $router->getControllerFactory());
    }

    public function testDefaultFactory(): void
    {
        $cache = $this->createMock(RouterCacheInterface::class);
        $router = new class($cache) extends AbstractRouter {
            public function dispatch(MiddlewareStack $middlewareStack): AbstractApivalkResponse {
                return $this->createMock(AbstractApivalkResponse::class);
            }
        };
        
        $this->assertInstanceOf(ApivalkControllerFactoryInterface::class, $router->getControllerFactory());
    }
}
