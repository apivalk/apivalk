<?php

declare(strict_types=1);

namespace Router;

use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Router\RouteRegexFactory;
use PHPUnit\Framework\TestCase;

class RouteRegexFactoryTest extends TestCase
{
    public function testGetUrlRegexPattern(): void
    {
        $method = new GetMethod();

        $route = new Route('/users', $method);
        $this->assertEquals('#^\/users$#', RouteRegexFactory::build($route));

        $route = new Route('/users/{id}', $method);
        $this->assertEquals('#^\/users\/([a-zA-Z0-9_-]+)$#', RouteRegexFactory::build($route));

        $route = new Route('/users/{id}/profile/{type}', $method);
        $this->assertEquals('#^\/users\/([a-zA-Z0-9_-]+)\/profile\/([a-zA-Z0-9_-]+)$#', RouteRegexFactory::build($route));
    }
}
