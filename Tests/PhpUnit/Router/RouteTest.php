<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Http\Method\PostMethod;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\RouteJsonSerializer;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Security\RouteAuthorization;

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
