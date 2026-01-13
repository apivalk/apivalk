<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;
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
use apivalk\apivalk\Security\Scope;
use PHPUnit\Framework\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    public function testPublicRoute(): void
    {
        $middleware = new SecurityMiddleware();
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);

        $route = $this->createMock(Route::class);
        $route->method('getSecurityRequirements')->willReturn([]);

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

        $scope = new Scope('read');
        $security = new SecurityRequirementObject('Bearer', [$scope]);
        $route = $this->createMock(Route::class);
        $route->method('getSecurityRequirements')->willReturn([$security]);

        $identity = $this->createMock(AbstractAuthIdentity::class);
        $identity->method('getGrantedScopes')->willReturn([$scope]);
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

        $scope = new Scope('read');
        $security = new SecurityRequirementObject('Bearer', [$scope]);
        $route = $this->createMock(Route::class);
        $route->method('getSecurityRequirements')->willReturn([$security]);

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

        $scopeRead = new Scope('read');
        $scopeWrite = new Scope('write');
        $security = new SecurityRequirementObject('Bearer', [$scopeWrite]);
        $route = $this->createMock(Route::class);
        $route->method('getSecurityRequirements')->willReturn([$security]);

        $identity = $this->createMock(AbstractAuthIdentity::class);
        $identity->method('isAuthenticated')->willReturn(true);
        $identity->method('getGrantedScopes')->willReturn([$scopeRead]);
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

        $scope = new Scope('read');
        $securityRequired = new SecurityRequirementObject('Bearer', [$scope]);
        $securityOptional = new SecurityRequirementObject(); // Empty = {} (no security required)

        $route = $this->createMock(Route::class);
        $route->method('getSecurityRequirements')->willReturn([$securityRequired, $securityOptional]);

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
