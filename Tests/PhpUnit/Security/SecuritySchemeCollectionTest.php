<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Security;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecuritySchemeObject;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Security\RouteAuthorization;
use apivalk\apivalk\Security\SecuritySchemeCollection;
use PHPUnit\Framework\TestCase;

class SecuritySchemeCollectionTest extends TestCase
{
    public function testAddAndGet(): void
    {
        $scheme = SecuritySchemeObject::http('bearer', 'bearer');

        $collection = new SecuritySchemeCollection();
        $collection->add($scheme);

        $this->assertTrue($collection->has('bearer'));
        $this->assertFalse($collection->has('apiKey'));
        $this->assertSame($scheme, $collection->get('bearer'));
    }

    public function testDuplicateNameThrows(): void
    {
        $collection = new SecuritySchemeCollection();
        $collection->add(SecuritySchemeObject::http('bearer', 'bearer'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('"bearer" is already registered');

        $collection->add(SecuritySchemeObject::apiKey('bearer', 'header'));
    }

    public function testUnknownNameThrowsWithTheName(): void
    {
        $collection = new SecuritySchemeCollection();
        $collection->add(SecuritySchemeObject::http('bearer', 'bearer'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown security scheme "apiKey"');

        $collection->get('apiKey');
    }

    public function testAllKeepsInsertionOrder(): void
    {
        $collection = new SecuritySchemeCollection();
        $collection->add(SecuritySchemeObject::http('bearer', 'bearer'));
        $collection->add(SecuritySchemeObject::apiKey('apiKey', 'header'));
        $collection->add(SecuritySchemeObject::http('basic', 'basic'));

        $this->assertSame(['bearer', 'apiKey', 'basic'], \array_keys($collection->all()));
    }

    public function testAssertRoutesResolvable_passesForKnownSchemesAndPublicRoutes(): void
    {
        $this->expectNotToPerformAssertions();

        $collection = new SecuritySchemeCollection();
        $collection->add(SecuritySchemeObject::http('bearer', 'bearer'));

        $router = $this->routerWithRoutes([
            $this->mockRoute('/v1/api/customers', new RouteAuthorization('bearer')),
            $this->mockRoute('/v1/api/status', null),
        ]);

        $collection->assertRoutesResolvable($router);
    }

    public function testAssertRoutesResolvable_throwsWithRouteAndSchemeName(): void
    {
        $collection = new SecuritySchemeCollection();
        $collection->add(SecuritySchemeObject::http('bearer', 'bearer'));

        $router = $this->routerWithRoutes([
            $this->mockRoute('/v1/api/customers', new RouteAuthorization('oauth2')),
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Route "/v1/api/customers" requires the security scheme "oauth2"');

        $collection->assertRoutesResolvable($router);
    }

    /** @param Route[] $routes */
    private function routerWithRoutes(array $routes): AbstractRouter
    {
        $entries = [];
        foreach ($routes as $route) {
            $entries[] = ['route' => $route, 'controllerClass' => 'StubController'];
        }

        $router = $this->createMock(AbstractRouter::class);
        $router->method('getRoutes')->willReturn($entries);

        return $router;
    }

    private function mockRoute(string $url, ?RouteAuthorization $routeAuthorization): Route
    {
        $route = $this->createMock(Route::class);
        $route->method('getUrl')->willReturn($url);
        $route->method('getRouteAuthorization')->willReturn($routeAuthorization);

        return $route;
    }
}
