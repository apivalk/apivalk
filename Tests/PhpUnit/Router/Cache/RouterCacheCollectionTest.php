<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Cache;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\Cache\RouterCacheCollection;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Router\Cache\RouterCacheEntry;

class RouterCacheCollectionTest extends TestCase
{
    public function testAddRouteCacheEntry(): void
    {
        $collection = new RouterCacheCollection();
        $route = $this->createMock(Route::class);
        $route->method('getUrl')->willReturn('/users/{id}');
        
        $collection->addRouteCacheEntry($route, 'UserController');
        
        $entries = $collection->getRouteCacheEntries();
        $this->assertCount(1, $entries);
        $this->assertInstanceOf(RouterCacheEntry::class, $entries[0]);
        $this->assertEquals('#^\/users\/([a-zA-Z0-9_-]+)$#', $entries[0]->getRegex());
    }

    public function testByJson(): void
    {
        $json = json_encode([
            [
                'controllerClass' => 'TestController',
                'route' => [
                    'url' => '/test',
                    'method' => 'GET',
                    'description' => null,
                    'tags' => [],
                    'securityRequirements' => []
                ]
            ]
        ]);
        
        $collection = RouterCacheCollection::byJson($json);
        $entries = $collection->getRouteCacheEntries();
        
        $this->assertCount(1, $entries);
        $this->assertEquals('TestController', $entries[0]->getControllerClass());
        $this->assertEquals('/test', $entries[0]->getRoute()->getUrl());
    }
}
