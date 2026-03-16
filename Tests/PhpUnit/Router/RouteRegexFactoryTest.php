<?php

declare(strict_types=1);

namespace Router;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\StringProperty;
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

    public function testBuildWithDocumentationPattern(): void
    {
        $method = new GetMethod();
        $route = new Route('/assets/{identifier}/health', $method);

        $documentation = new ApivalkRequestDocumentation();
        $documentation->addPathProperty(
            (new StringProperty('identifier', 'ID or EVSE ID'))->setPattern('[^/]+')
        );

        $regex = RouteRegexFactory::build($route, $documentation);

        $this->assertEquals('#^\/assets\/([^/]+)\/health$#', $regex);
        $this->assertRegExp($regex, '/assets/123/health');
        $this->assertRegExp($regex, '/assets/DE%2ATST%2AETHG_0002%2A00101/health');
        $this->assertRegExp($regex, '/assets/DE*TST*ETHG_0002*00101/health');
        $this->assertNotRegExp($regex, '/assets/foo/bar/health');
    }

    public function testBuildWithoutDocumentationUsesDefault(): void
    {
        $method = new GetMethod();
        $route = new Route('/users/{id}', $method);

        // Without documentation → default regex
        $regex = RouteRegexFactory::build($route);
        $this->assertEquals('#^\/users\/([a-zA-Z0-9_-]+)$#', $regex);

        // With documentation but without pattern → also default
        $documentation = new ApivalkRequestDocumentation();
        $documentation->addPathProperty(new StringProperty('id', 'User ID'));
        $regex = RouteRegexFactory::build($route, $documentation);
        $this->assertEquals('#^\/users\/([a-zA-Z0-9_-]+)$#', $regex);
    }
}
