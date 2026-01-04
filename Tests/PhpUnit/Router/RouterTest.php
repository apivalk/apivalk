<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Router\RouteRegexFactory;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\Router;
use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Util\ClassLocator;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactoryInterface;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\MethodNotAllowedApivalkResponse;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;

class RouterTest extends TestCase
{
    private $cache;
    private $classLocator;
    private $controllerFactory;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->classLocator = $this->createMock(ClassLocator::class);
        $this->controllerFactory = $this->createMock(ApivalkControllerFactoryInterface::class);

        // Stub Cache to avoid building cache in constructor
        $this->cache->method('get')->with(AbstractRouter::CACHE_INDEX_KEY)
            ->willReturn(new CacheItem(AbstractRouter::CACHE_INDEX_KEY, '[]'));
    }

    public function testDispatchNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/not-found';

        $router = new Router($this->classLocator, $this->cache, $this->controllerFactory);
        $response = $router->dispatch(new MiddlewareStack());

        $this->assertInstanceOf(NotFoundApivalkResponse::class, $response);
    }

    public function testDispatchMethodNotAllowed(): void
    {
        $route = new Route('/test', new GetMethod());
        $controllerClass = 'TestController';

        $indexData = [
            [
                'regex' => RouteRegexFactory::build($route),
                'method' => 'GET',
                'key' => 'route_key',
                'controllerClass' => $controllerClass
            ]
        ];

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturnCallback(function ($key) use ($indexData, $route) {
            if ($key === AbstractRouter::CACHE_INDEX_KEY) {
                return new CacheItem(AbstractRouter::CACHE_INDEX_KEY, json_encode($indexData));
            }
            if ($key === 'route_key') {
                return new CacheItem('route_key', json_encode($route));
            }
            return null;
        });

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test';

        $router = new Router($this->classLocator, $cache, $this->controllerFactory);
        $response = $router->dispatch(new MiddlewareStack());

        $this->assertInstanceOf(MethodNotAllowedApivalkResponse::class, $response);
    }

    public function testDispatchSuccess(): void
    {
        $route = new Route('/test', new GetMethod());

        // Use a concrete controller class for mocking since static methods are hard to mock in PHPUnit 7
        if (!class_exists('TestControllerForRouterTest')) {
            eval(
            '
                class TestRequestForRouterTest extends apivalk\apivalk\Http\Request\AbstractApivalkRequest {
                    public static function getDocumentation(): apivalk\apivalk\Documentation\ApivalkRequestDocumentation {
                        return new apivalk\apivalk\Documentation\ApivalkRequestDocumentation();
                    }
                }

                class TestControllerForRouterTest extends apivalk\apivalk\Http\Controller\AbstractApivalkController {
                public function __invoke(\apivalk\apivalk\Http\Request\ApivalkRequestInterface $request): \apivalk\apivalk\Http\Response\AbstractApivalkResponse { return new apivalk\apivalk\Http\Response\NotFoundApivalkResponse(); }
                public static function getRoute(): \apivalk\apivalk\Router\Route { return new \apivalk\apivalk\Router\Route("/", new \apivalk\apivalk\Http\Method\GetMethod()); }
                public static function getRequestClass(): string { return "TestRequestForRouterTest"; }
                public static function getResponseClasses(): array { return []; }
            }'
            );
        }
        $controllerClass = 'TestControllerForRouterTest';

        $indexData = [
            [
                'regex' => RouteRegexFactory::build($route),
                'method' => 'GET',
                'key' => 'route_key',
                'controllerClass' => $controllerClass
            ]
        ];

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturnCallback(function ($key) use ($indexData, $route) {
            if ($key === AbstractRouter::CACHE_INDEX_KEY) {
                return new CacheItem(AbstractRouter::CACHE_INDEX_KEY, json_encode($indexData));
            }
            if ($key === 'route_key') {
                return new CacheItem('route_key', json_encode($route));
            }
            return null;
        });

        $controller = $this->getMockBuilder($controllerClass)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controllerFactory->method('create')->with($controllerClass)->willReturn($controller);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';

        $expectedResponse = $this->createMock(AbstractApivalkResponse::class);
        $middlewareStack = $this->createMock(MiddlewareStack::class);
        $middlewareStack->method('handle')->willReturn($expectedResponse);

        $router = new Router($this->classLocator, $cache, $this->controllerFactory);
        $response = $router->dispatch($middlewareStack);

        $this->assertSame($expectedResponse, $response);
    }

    public function testGetRoutes(): void
    {
        $route = new Route('/test', new GetMethod());
        $controllerClass = 'TestController';

        $indexData = [
            [
                'regex' => RouteRegexFactory::build($route),
                'method' => 'GET',
                'key' => 'route_key',
                'controllerClass' => $controllerClass
            ]
        ];

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturnCallback(function ($key) use ($indexData, $route) {
            if ($key === AbstractRouter::CACHE_INDEX_KEY) {
                return new CacheItem(AbstractRouter::CACHE_INDEX_KEY, json_encode($indexData));
            }
            if ($key === 'route_key') {
                return new CacheItem('route_key', json_encode($route));
            }
            return null;
        });

        $router = new Router($this->classLocator, $cache, $this->controllerFactory);
        $routes = $router->getRoutes();

        $this->assertCount(1, $routes);
        $this->assertEquals($route->getUrl(), $routes[0]['route']->getUrl());
        $this->assertEquals($controllerClass, $routes[0]['controllerClass']);
    }
}
