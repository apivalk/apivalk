<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Router\RouteJsonSerializer;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;
use apivalk\apivalk\Security\Scope;

class RouteTest extends TestCase
{
    public function testGetters(): void
    {
        $method = new GetMethod();
        $tag = new TagObject('user');
        $security = new SecurityRequirementObject('Bearer');

        $route = new Route('/users', $method, 'Description', [$tag], [$security]);

        $this->assertEquals('/users', $route->getUrl());
        $this->assertSame($method, $route->getMethod());
        $this->assertEquals('Description', $route->getDescription());
        $this->assertEquals([$tag], $route->getTags());
        $this->assertEquals([$security], $route->getSecurityRequirements());
    }

    public function testJsonSerialization(): void
    {
        $method = new GetMethod();
        $tag = new TagObject('user', 'User tag');
        $scope = new Scope('read');
        $security = new SecurityRequirementObject('Bearer', [$scope]);

        $route = new Route('/users', $method, 'Desc', [$tag], [$security]);

        $json = json_encode(RouteJsonSerializer::serialize($route));
        $this->assertInternalType('string', $json);

        $newRoute = RouteJsonSerializer::deserialize($json);

        $this->assertEquals('/users', $newRoute->getUrl());
        $this->assertEquals('GET', $newRoute->getMethod()->getName());
        $this->assertEquals('Desc', $newRoute->getDescription());
        $this->assertCount(1, $newRoute->getTags());
        $this->assertEquals('user', $newRoute->getTags()[0]->getName());
        $this->assertCount(1, $newRoute->getSecurityRequirements());
        $this->assertEquals('Bearer', $newRoute->getSecurityRequirements()[0]->getName());
        $this->assertEquals('read', $newRoute->getSecurityRequirements()[0]->getScopes()[0]->getName());
    }
}
