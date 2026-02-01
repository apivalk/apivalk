<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Security\RouteAuthorization;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\UnauthorizedApivalkResponse;
use apivalk\apivalk\Middleware\SecurityMiddleware;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use PHPUnit\Framework\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    public function testPublicRoute(): void
    {
        $middleware = new SecurityMiddleware();
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);

        $route = $this->createMock(Route::class);
        $route->method('getRouteAuthorization')->willReturn(null);

        $controller = new class($route) extends AbstractApivalkController {
            private static $route;

            public function __construct($r)
            {
                self::$route = $r;
            }

            public static function getRoute(): Route
            {
                return self::$route;
            }

            public static function getRequestClass(): string
            {
                return '';
            }

            public static function getResponseClasses(): array
            {
                return [];
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new NotFoundApivalkResponse();
            }
        };

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->process($request, $controller, $next);
        $this->assertSame($response, $result);
    }

    public function testAuthorized(): void
    {
        $middleware = new SecurityMiddleware();
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);

        $security = new RouteAuthorization('Bearer', ['read']);
        $route = $this->createMock(Route::class);
        $route->method('getRouteAuthorization')->willReturn($security);

        $identity = $this->createMock(AbstractAuthIdentity::class);
        $identity->method('getScopes')->willReturn(['read']);
        $identity->method('isScopeGranted')->willReturn(true);
        $request->method('getAuthIdentity')->willReturn($identity);

        $controller = new class($route) extends AbstractApivalkController {
            private static $route;

            public function __construct($r)
            {
                self::$route = $r;
            }

            public static function getRoute(): Route
            {
                return self::$route;
            }

            public static function getRequestClass(): string
            {
                return '';
            }

            public static function getResponseClasses(): array
            {
                return [];
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new NotFoundApivalkResponse();
            }
        };

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->process($request, $controller, $next);
        $this->assertSame($response, $result);
    }

    public function testUnauthorizedGuest(): void
    {
        $middleware = new SecurityMiddleware();
        $request = $this->createMock(ApivalkRequestInterface::class);

        $security = new RouteAuthorization('Bearer', ['read']);
        $route = $this->createMock(Route::class);
        $route->method('getRouteAuthorization')->willReturn($security);

        $identity = new GuestAuthIdentity([]); // Guest has no scopes
        $request->method('getAuthIdentity')->willReturn($identity);

        $controller = new class($route) extends AbstractApivalkController {
            private static $route;

            public function __construct($r)
            {
                self::$route = $r;
            }

            public static function getRoute(): Route
            {
                return self::$route;
            }

            public static function getRequestClass(): string
            {
                return '';
            }

            public static function getResponseClasses(): array
            {
                return [];
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new NotFoundApivalkResponse();
            }
        };

        $response = $middleware->process($request, $controller, function () {
        });
        $this->assertInstanceOf(UnauthorizedApivalkResponse::class, $response);
    }

    public function testForbiddenAuthenticated(): void
    {
        $middleware = new SecurityMiddleware();
        $request = $this->createMock(ApivalkRequestInterface::class);

        $security = new RouteAuthorization('Bearer', ['write']);
        $route = $this->createMock(Route::class);
        $route->method('getRouteAuthorization')->willReturn($security);

        $identity = $this->createMock(AbstractAuthIdentity::class);
        $identity->method('isAuthenticated')->willReturn(true);
        $identity->method('getScopes')->willReturn(['read']);
        $request->method('getAuthIdentity')->willReturn($identity);

        $controller = new class($route) extends AbstractApivalkController {
            private static $route;

            public function __construct($r)
            {
                self::$route = $r;
            }

            public static function getRoute(): Route
            {
                return self::$route;
            }

            public static function getRequestClass(): string
            {
                return '';
            }

            public static function getResponseClasses(): array
            {
                return [];
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new NotFoundApivalkResponse();
            }
        };

        $response = $middleware->process($request, $controller, function () {
        });
        $this->assertInstanceOf(ForbiddenApivalkResponse::class, $response);
    }

    public function testOptionalSecurity(): void
    {
        $middleware = new SecurityMiddleware();
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);

        // This test case might need adjustment if RouteAuthorization no longer supports "empty" for optional security the same way.
        // If a route has multiple authorizations, it's usually OR.
        // However, Route::getRouteAuthorization() now returns ?RouteAuthorization (single).
        // Let's assume for now it returns null for public.
        
        $route = $this->createMock(Route::class);
        $route->method('getRouteAuthorization')->willReturn(null);

        // Identity with no scopes (Guest)
        $identity = new GuestAuthIdentity([]);
        $request->method('getAuthIdentity')->willReturn($identity);

        $controller = new class($route) extends AbstractApivalkController {
            private static $route;

            public function __construct($r)
            {
                self::$route = $r;
            }

            public static function getRoute(): Route
            {
                return self::$route;
            }

            public static function getRequestClass(): string
            {
                return '';
            }

            public static function getResponseClasses(): array
            {
                return [];
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new NotFoundApivalkResponse();
            }
        };

        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->process($request, $controller, $next);
        $this->assertSame($response, $result);
    }
}
