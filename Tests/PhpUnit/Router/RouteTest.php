<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;

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
        $security = new SecurityRequirementObject('Bearer', ['read']);
        
        $route = new Route('/users', $method, 'Desc', [$tag], [$security]);
        
        $json = json_encode($route);
        $this->assertInternalType('string', $json);
        
        $newRoute = Route::byJson($json);
        
        $this->assertEquals('/users', $newRoute->getUrl());
        $this->assertEquals('GET', $newRoute->getMethod()->getName());
        $this->assertEquals('Desc', $newRoute->getDescription());
        $this->assertCount(1, $newRoute->getTags());
        $this->assertEquals('user', $newRoute->getTags()[0]->getName());
        $this->assertCount(1, $newRoute->getSecurityRequirements());
        $this->assertEquals('Bearer', $newRoute->getSecurityRequirements()[0]->getName());
    }
}
