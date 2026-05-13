<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Method\PostMethod;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\RouteJsonSerializer;
use apivalk\apivalk\Security\RouteAuthorization;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testGetters(): void
    {
        $method = new GetMethod();
        $tag = new TagObject('user');
        $security = new RouteAuthorization('Bearer');

        $route = new Route('/users', $method, 'Description', null, [$tag], $security);

        $this->assertEquals('/users', $route->getUrl());
        $this->assertSame($method, $route->getMethod());
        $this->assertEquals('Description', $route->getDescription());
        $this->assertEquals([$tag], $route->getTags());
        $this->assertSame($security, $route->getRouteAuthorization());
    }

    public function testFluentBuilderApi(): void
    {
        $getRoute = new Route('/users', new GetMethod(), 'Description', 's');
        $this->assertEquals($getRoute, Route::get('/users')->summary('s')->description('Description'));

        $getRoute = new Route('/users', new GetMethod());
        $this->assertEquals($getRoute, Route::get('/users'));

        $postRoute = new Route(
            '/cr/{d}',
            new PostMethod(),
            'Nice',
            null,
            [new TagObject('abc')],
            new RouteAuthorization('api', ['test'], ['test:read']),
            new IpRateLimit('test', 20, 5)
        );
        $this->assertEquals(
            $postRoute,
            Route::post('/cr/{d}')
                ->description('Nice')
                ->tags([new TagObject('abc')])
                ->rateLimit(new IpRateLimit('test', 20, 5))
                ->routeAuthorization(new RouteAuthorization('api', ['test'], ['test:read']))
        );
    }

    public function testPathProperty(): void
    {
        $route = Route::get('/users/{id}')->pathProperty(new IntegerProperty('id', 'ID'));

        $this->assertCount(1, $route->getPathProperties());
        $this->assertArrayHasKey('id', $route->getPathProperties());

        $route2 = Route::get('/orgs/{org}/users/{user}')
            ->pathProperty(new StringProperty('org', 'Org'))
            ->pathProperty(new IntegerProperty('user', 'User'));

        $this->assertCount(2, $route2->getPathProperties());
        $this->assertArrayHasKey('org', $route2->getPathProperties());
        $this->assertArrayHasKey('user', $route2->getPathProperties());
    }

    public function testPathPropertyJsonRoundTrip(): void
    {
        $route = Route::get('/sessions/{session_uuid}')
            ->pathProperty(new StringProperty('session_uuid', 'Session UUID'));

        $json = json_encode(RouteJsonSerializer::serialize($route));
        $this->assertIsString($json);

        $restored = RouteJsonSerializer::deserialize($json);

        $this->assertCount(1, $restored->getPathProperties());
        $this->assertArrayHasKey('session_uuid', $restored->getPathProperties());
        $this->assertEquals('session_uuid', $restored->getPathProperties()['session_uuid']->getPropertyName());
    }

    public function testJsonSerialization(): void
    {
        $method = new GetMethod();
        $tag = new TagObject('user', 'User tag');
        $security = new RouteAuthorization('Bearer', ['read']);

        $route = new Route('/users', $method, 'Desc', null, [$tag], $security);

        $json = json_encode(RouteJsonSerializer::serialize($route));
        $this->assertIsString($json);

        $newRoute = RouteJsonSerializer::deserialize($json);

        $this->assertEquals('/users', $newRoute->getUrl());
        $this->assertEquals('GET', $newRoute->getMethod()->getName());
        $this->assertEquals('Desc', $newRoute->getDescription());
        $this->assertCount(1, $newRoute->getTags());
        $this->assertEquals('user', $newRoute->getTags()[0]->getName());
        $this->assertInstanceOf(RouteAuthorization::class, $newRoute->getRouteAuthorization());
        $this->assertEquals('Bearer', $newRoute->getRouteAuthorization()->getSecuritySchemeName());
        $this->assertEquals('read', $newRoute->getRouteAuthorization()->getRequiredScopes()[0]);
    }
}
