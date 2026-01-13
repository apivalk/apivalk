<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\TooManyRequestsApivalkResponse;
use apivalk\apivalk\Middleware\RateLimitMiddleware;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route;
use PHPUnit\Framework\TestCase;

class RateLimitMiddlewareTest extends TestCase
{
    private $cache;
    private $middleware;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->middleware = new RateLimitMiddleware($this->cache);
    }

    public function testProcessWithoutRateLimit(): void
    {
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);
        
        $route = $this->createMock(Route::class);
        $route->method('getRateLimit')->willReturn(null);

        $controller = new class($route) extends AbstractApivalkController {
            private static $routeInstance;
            public function __construct($route) { self::$routeInstance = $route; }
            public static function getRoute(): Route { return self::$routeInstance; }
            public static function getRequestClass(): string { return ''; }
            public static function getResponseClasses(): array { return []; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse { return $this->createMock(AbstractApivalkResponse::class); }
        };

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $this->middleware->process($request, $controller, $next);

        $this->assertSame($response, $result);
    }

    public function testProcessWithRateLimitAllowed(): void
    {
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);
        
        $rateLimit = $this->createMock(RateLimitInterface::class);
        $rateLimit->method('getKey')->willReturn('key');
        $rateLimit->method('getMaxAttempts')->willReturn(10);
        $rateLimit->method('getWindowInSeconds')->willReturn(60);

        $route = $this->createMock(Route::class);
        $route->method('getRateLimit')->willReturn($rateLimit);

        $controller = new class($route) extends AbstractApivalkController {
            private static $routeInstance;
            public function __construct($route) { self::$routeInstance = $route; }
            public static function getRoute(): Route { return self::$routeInstance; }
            public static function getRequestClass(): string { return ''; }
            public static function getResponseClasses(): array { return []; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse { return $this->createMock(AbstractApivalkResponse::class); }
        };

        $this->cache->method('get')->willReturn(null);

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $this->middleware->process($request, $controller, $next);

        $this->assertSame($response, $result);
    }

    public function testProcessWithRateLimitExceeded(): void
    {
        $request = $this->createMock(ApivalkRequestInterface::class);
        
        $rateLimit = $this->createMock(RateLimitInterface::class);
        $rateLimit->method('getKey')->willReturn('key');
        $rateLimit->method('getMaxAttempts')->willReturn(1);
        $rateLimit->method('getWindowInSeconds')->willReturn(60);

        $route = $this->createMock(Route::class);
        $route->method('getRateLimit')->willReturn($rateLimit);

        $controller = new class($route) extends AbstractApivalkController {
            private static $routeInstance;
            public function __construct($route) { self::$routeInstance = $route; }
            public static function getRoute(): Route { return self::$routeInstance; }
            public static function getRequestClass(): string { return ''; }
            public static function getResponseClasses(): array { return []; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse { return $this->createMock(AbstractApivalkResponse::class); }
        };

        // Mock cache to return item that makes value exceed limit
        $cacheItem = new \apivalk\apivalk\Cache\CacheItem('key', 1, 60);
        $this->cache->method('get')->willReturn($cacheItem);

        $next = function ($req) {
            $this->fail('Next should not be called');
        };

        $result = $this->middleware->process($request, $controller, $next);

        $this->assertInstanceOf(TooManyRequestsApivalkResponse::class, $result);
    }
}
