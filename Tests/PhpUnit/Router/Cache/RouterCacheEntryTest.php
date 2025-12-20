<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Cache;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\Cache\RouterCacheEntry;
use apivalk\apivalk\Router\Route;

class RouterCacheEntryTest extends TestCase
{
    public function testGetters(): void
    {
        $route = $this->createMock(Route::class);
        $entry = new RouterCacheEntry($route, '/^regex$/', 'MyController');
        
        $this->assertSame($route, $entry->getRoute());
        $this->assertEquals('/^regex$/', $entry->getRegex());
        $this->assertEquals('MyController', $entry->getControllerClass());
    }

    public function testJsonSerialize(): void
    {
        $route = $this->createMock(Route::class);
        $entry = new RouterCacheEntry($route, '/^regex$/', 'MyController');
        
        $serialized = $entry->jsonSerialize();
        $this->assertSame($route, $serialized['route']);
        $this->assertEquals('/^regex$/', $serialized['regex']);
        $this->assertEquals('MyController', $serialized['controllerClass']);
    }
}
