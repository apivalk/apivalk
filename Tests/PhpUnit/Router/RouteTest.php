<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

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

        $route = new Route('/users', $method, 'Description', [$tag], $security);

        $this->assertEquals('/users', $route->getUrl());
        $this->assertSame($method, $route->getMethod());
        $this->assertEquals('Description', $route->getDescription());
        $this->assertEquals([$tag], $route->getTags());
        $this->assertSame($security, $route->getRouteAuthorization());
    }

    public function testJsonSerialization(): void
    {
        $method = new GetMethod();
        $tag = new TagObject('user', 'User tag');
        $security = new RouteAuthorization('Bearer', ['read']);

        $route = new Route('/users', $method, 'Desc', [$tag], $security);

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
