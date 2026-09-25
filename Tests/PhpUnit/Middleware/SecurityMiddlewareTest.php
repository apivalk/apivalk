<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecuritySchemeObject;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\UnauthorizedApivalkResponse;
use apivalk\apivalk\Middleware\SecurityMiddleware;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\JwtAuthIdentity;
use apivalk\apivalk\Security\RouteAuthorization;
use apivalk\apivalk\Security\SecuritySchemeCollection;
use PHPUnit\Framework\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    private SecurityMiddleware $middleware;

    protected function setUp(): void
    {
        $schemes = new SecuritySchemeCollection();
        $schemes->add(SecuritySchemeObject::http('Bearer', 'bearer'));
        $schemes->add(SecuritySchemeObject::http('BearerDev', 'bearer', null, null, ['dev-client', 'mobile-client']));

        $this->middleware = new SecurityMiddleware($schemes);
    }

    public function testPublicRoute_allowsEveryone(): void
    {
        $route = $this->mockRoute(null);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $expected = $this->createMock(AbstractApivalkResponse::class);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn(ApivalkRequestInterface $r) => $expected
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
            static fn() => new NotFoundApivalkResponse()
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
            static fn(ApivalkRequestInterface $r) => $expected
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
            static fn() => new NotFoundApivalkResponse()
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
            static fn() => new NotFoundApivalkResponse()
        );

        self::assertInstanceOf(ForbiddenApivalkResponse::class, $result);
    }

    public function testMissingPermission_returnsForbidden_forAuthenticated(): void
    {
        $routeAuthorization = new RouteAuthorization('Bearer', [], ['asset:update']);
        $route = $this->mockRoute($routeAuthorization);

        $identity = $this->createMock(AbstractAuthIdentity::class);
        $identity->method('isAuthenticated')->willReturn(true);
        $identity->method('isPermissionGranted')->with('asset:update')->willReturn(false);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn() => new NotFoundApivalkResponse()
        );

        self::assertInstanceOf(ForbiddenApivalkResponse::class, $result);
    }

    public function testMissingPermission_returnsUnauthorized_forGuest(): void
    {
        $routeAuthorization = new RouteAuthorization('Bearer', [], ['asset:update']);
        $route = $this->mockRoute($routeAuthorization);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn(new GuestAuthIdentity([]));

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn() => new NotFoundApivalkResponse()
        );

        self::assertInstanceOf(UnauthorizedApivalkResponse::class, $result);
    }

    public function testSchemeAudienceGranted_callsNext(): void
    {
        $routeAuthorization = new RouteAuthorization('BearerDev', [], ['asset:update']);
        $route = $this->mockRoute($routeAuthorization);

        $identity = new JwtAuthIdentity(null, null, null, [], ['asset:update'], ['dev-client']);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);
        $expected = $this->createMock(AbstractApivalkResponse::class);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn(ApivalkRequestInterface $r) => $expected
        );

        self::assertSame($expected, $result);
    }

    public function testOneOfSeveralSchemeAudiences_isEnough(): void
    {
        $routeAuthorization = new RouteAuthorization('BearerDev', [], ['asset:update']);
        $route = $this->mockRoute($routeAuthorization);

        $identity = new JwtAuthIdentity(null, null, null, [], ['asset:update'], ['staging-client', 'mobile-client']);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);
        $expected = $this->createMock(AbstractApivalkResponse::class);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn(ApivalkRequestInterface $r) => $expected
        );

        self::assertSame($expected, $result);
    }

    public function testWrongAudience_returnsUnauthorized(): void
    {
        $routeAuthorization = new RouteAuthorization('BearerDev', [], ['asset:update']);
        $route = $this->mockRoute($routeAuthorization);

        $identity = new JwtAuthIdentity(null, null, null, [], ['asset:update'], ['staging-client']);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn() => new NotFoundApivalkResponse()
        );

        self::assertInstanceOf(UnauthorizedApivalkResponse::class, $result);
    }

    public function testTokenWithoutAudiences_isRejected_whenSchemeRequiresOne(): void
    {
        $routeAuthorization = new RouteAuthorization('BearerDev', [], ['asset:update']);
        $route = $this->mockRoute($routeAuthorization);

        $identity = new JwtAuthIdentity(null, null, null, [], ['asset:update']);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn() => new NotFoundApivalkResponse()
        );

        self::assertInstanceOf(UnauthorizedApivalkResponse::class, $result);
    }

    /**
     * A foreign token must not learn which scope it is missing, so the audience decides first.
     */
    public function testWrongAudienceAndMissingScope_returnsUnauthorizedNotForbidden(): void
    {
        $routeAuthorization = new RouteAuthorization('BearerDev', ['write'], []);
        $route = $this->mockRoute($routeAuthorization);

        $identity = new JwtAuthIdentity(null, null, null, [], [], ['staging-client']);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);

        $result = $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn() => new NotFoundApivalkResponse()
        );

        self::assertInstanceOf(UnauthorizedApivalkResponse::class, $result);
    }

    public function testUnknownSecurityScheme_throws(): void
    {
        $route = $this->mockRoute(new RouteAuthorization('NotRegistered'));

        $identity = new JwtAuthIdentity(null, null, null, [], []);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getAuthIdentity')->willReturn($identity);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('NotRegistered');

        $this->middleware->process(
            $request,
            $this->controllerFor($route),
            static fn() => new NotFoundApivalkResponse()
        );
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
            private static Route $route;

            public function __construct(Route $route)
            {
                self::$route = $route;
            }

            public static function getRoute(): Route
            {
                return self::$route;
            }



            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new NotFoundApivalkResponse();
            }
        };
    }
}
