<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\Router;
use apivalk\apivalk\Router\Cache\RouterCacheInterface;
use apivalk\apivalk\Router\Cache\RouterCacheCollection;
use apivalk\apivalk\Router\Cache\RouterCacheEntry;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactoryInterface;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\MethodNotAllowedApivalkResponse;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;

class RouterTest extends TestCase
{
    private $serverBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
    }

    public function testDispatchNotFound(): void
    {
        $collection = new RouterCacheCollection();
        $cache = $this->createMock(RouterCacheInterface::class);
        $cache->method('getRouterCacheCollection')->willReturn($collection);
        
        $router = new Router($cache);
        $middleware = $this->createMock(MiddlewareStack::class);
        
        $response = $router->dispatch($middleware);
        $this->assertInstanceOf(NotFoundApivalkResponse::class, $response);
    }

    public function testDispatchMethodNotAllowed(): void
    {
        $route = new Route('/test', new GetMethod());
        $collection = new RouterCacheCollection();
        $collection->addRouteCacheEntry($route, 'TestController');
        
        $cache = $this->createMock(RouterCacheInterface::class);
        $cache->method('getRouterCacheCollection')->willReturn($collection);
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $router = new Router($cache);
        $middleware = $this->createMock(MiddlewareStack::class);
        
        $response = $router->dispatch($middleware);
        $this->assertInstanceOf(MethodNotAllowedApivalkResponse::class, $response);
    }

    public function testDispatchSuccess(): void
    {
        $route = new Route('/test', new GetMethod());
        
        $request = new class implements ApivalkRequestInterface {
            public static function getDocumentation(): \apivalk\apivalk\Documentation\ApivalkRequestDocumentation {
                return new \apivalk\apivalk\Documentation\ApivalkRequestDocumentation();
            }
            public function populate(Route $route): void {}
            public function getMethod(): \apivalk\apivalk\Http\Method\MethodInterface { return new GetMethod(); }
            public function header(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return new \apivalk\apivalk\Http\Request\Parameter\ParameterBag(); }
            public function query(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return new \apivalk\apivalk\Http\Request\Parameter\ParameterBag(); }
            public function body(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return new \apivalk\apivalk\Http\Request\Parameter\ParameterBag(); }
            public function path(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return new \apivalk\apivalk\Http\Request\Parameter\ParameterBag(); }
            public function file(): \apivalk\apivalk\Http\Request\File\FileBag { return new \apivalk\apivalk\Http\Request\File\FileBag(); }
            public function getAuthIdentity(): ?\apivalk\apivalk\Security\AbstractAuthIdentity { return null; }
            public function setAuthIdentity(?\apivalk\apivalk\Security\AbstractAuthIdentity $authIdentity): void {}
        };
        $requestClass = get_class($request);

        $controllerClass = get_class(new class($requestClass) extends AbstractApivalkController {
            private static $req;
            public function __construct($req = null) { if($req) self::$req = $req; }
            public static function getRoute(): Route { return new Route('/test', new GetMethod()); }
            public static function getRequestClass(): string { return self::$req; }
            public static function getResponseClasses(): array { return []; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse {
                return $this->createMock(AbstractApivalkResponse::class);
            }
        });

        $collection = new RouterCacheCollection();
        $collection->addRouteCacheEntry($route, $controllerClass);
        
        $cache = $this->createMock(RouterCacheInterface::class);
        $cache->method('getRouterCacheCollection')->willReturn($collection);
        
        $controller = $this->createMock(AbstractApivalkController::class);
        $factory = $this->createMock(ApivalkControllerFactoryInterface::class);
        $factory->method('create')->with($controllerClass)->willReturn($controller);

        $router = new Router($cache, $factory);
        $middleware = $this->createMock(MiddlewareStack::class);
        $expectedResponse = $this->createMock(AbstractApivalkResponse::class);
        $middleware->method('handle')->willReturn($expectedResponse);
        
        $response = $router->dispatch($middleware);
        $this->assertSame($expectedResponse, $response);
    }
}
