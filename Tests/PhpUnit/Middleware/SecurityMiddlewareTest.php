<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

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
use apivalk\apivalk\Security\RouteAuthorization;
use PHPUnit\Framework\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    /** @var SecurityMiddleware */
    private $middleware;

    protected function setUp(): void
    {
        $this->middleware = new SecurityMiddleware();
    }

    public function testPublicRoute_allowsEveryone(): void
    {
        $route = $this->mockRoute(null);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $expected = $this->createMock(AbstractApivalkResponse::class);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static function (ApivalkRequestInterface $r) use ($expected) {
                return $expected;
            }
        );

        self::assertSame($expected, $result);
    }

    public function testProtectedRoute_requiresAuthenticated_evenIfNoRequirements(): void
    {
        $routeAuthorization = new RouteAuthorization('Bearer'); // not null => requires authenticated
        $route = $this->mockRoute($routeAuthorization);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn(new GuestAuthIdentity([]));

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static function () {
                return new NotFoundApivalkResponse();
            }
        );

        self::assertInstanceOf(UnauthorizedApivalkResponse::class, $result);
    }

    public function testAuthorized_whenAllScopesAndPermissionsGranted(): void
    {
        $routeAuthorization = new RouteAuthorization('Bearer', ['read'], ['asset:view']);
        $route = $this->mockRoute($routeAuthorization);

        $identity = $this->createMock(AbstractAuthIdentity::class);
        $identity->method('isAuthenticated')->willReturn(true);
        $identity->method('isScopeGranted')->willReturn(true);
        $identity->method('isPermissionGranted')->willReturn(true);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);

        $expected = $this->createMock(AbstractApivalkResponse::class);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static function (ApivalkRequestInterface $r) use ($expected) {
                return $expected;
            }
        );

        self::assertSame($expected, $result);
    }

    public function testMissingScope_returnsUnauthorized_forGuest(): void
    {
        $routeAuthorization = new RouteAuthorization('Bearer', ['read']);
        $route = $this->mockRoute($routeAuthorization);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn(new GuestAuthIdentity([]));

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static function () {
                return new NotFoundApivalkResponse();
            }
        );

        self::assertInstanceOf(UnauthorizedApivalkResponse::class, $result);
    }

    public function testMissingScope_returnsForbidden_forAuthenticated(): void
    {
        $routeAuthorization = new RouteAuthorization('Bearer', ['write']);
        $route = $this->mockRoute($routeAuthorization);

        $identity = $this->createMock(AbstractAuthIdentity::class);
        $identity->method('isAuthenticated')->willReturn(true);
        $identity->method('isScopeGranted')->with('write')->willReturn(false);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static function () {
                return new NotFoundApivalkResponse();
            }
        );

        self::assertInstanceOf(ForbiddenApivalkResponse::class, $result);
    }

    public function testMissingPermission_returnsForbidden_forAuthenticated(): void
    {
        $routeAuthorization = new RouteAuthorization('Bearer', [], ['asset:edit']);
        $route = $this->mockRoute($routeAuthorization);

        $identity = $this->createMock(AbstractAuthIdentity::class);
        $identity->method('isAuthenticated')->willReturn(true);
        $identity->method('isPermissionGranted')->with('asset:edit')->willReturn(false);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static function () {
                return new NotFoundApivalkResponse();
            }
        );

        self::assertInstanceOf(ForbiddenApivalkResponse::class, $result);
    }

    public function testMissingPermission_returnsUnauthorized_forGuest(): void
    {
        $routeAuthorization = new RouteAuthorization('Bearer', [], ['asset:edit']);
        $route = $this->mockRoute($routeAuthorization);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn(new GuestAuthIdentity([]));

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static function () {
                return new NotFoundApivalkResponse();
            }
        );

        self::assertInstanceOf(UnauthorizedApivalkResponse::class, $result);
    }

    /**
     * @param RouteAuthorization|null $routeAuthorization
     *
     * @return Route
     */
    private function mockRoute(?RouteAuthorization $routeAuthorization): Route
    {
        $route = $this->createMock(Route::class);
        $route->method('getRouteAuthorization')->willReturn($routeAuthorization);

        return $route;
    }

    private function controllerFor(Route $route): AbstractApivalkController
    {
        return new class($route) extends AbstractApivalkController {
            /** @var Route */
            private static $route;

            public function __construct(Route $route)
            {
                self::$route = $route;
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
    }
}
