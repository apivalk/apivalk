<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Security;

use apivalk\apivalk\Security\RouteAuthorization;
use PHPUnit\Framework\TestCase;

class RouteAuthorizationTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $securitySchemeName = 'BearerAuth';
        $scopes = ['read:users', 'write:users'];
        $permissions = ['admin'];

        $routeAuthorization = new RouteAuthorization($securitySchemeName, $scopes, $permissions);

        $this->assertEquals($securitySchemeName, $routeAuthorization->getSecuritySchemeName());
        $this->assertEquals($scopes, $routeAuthorization->getRequiredScopes());
        $this->assertEquals($permissions, $routeAuthorization->getRequiredPermissions());
    }

    public function testDefaultValues(): void
    {
        $securitySchemeName = 'ApiKey';
        $routeAuthorization = new RouteAuthorization($securitySchemeName);

        $this->assertEquals($securitySchemeName, $routeAuthorization->getSecuritySchemeName());
        $this->assertEquals([], $routeAuthorization->getRequiredScopes());
        $this->assertEquals([], $routeAuthorization->getRequiredPermissions());
    }

    public function testNullValuesBecomeEmptyArrays(): void
    {
        $securitySchemeName = 'OAuth2';
        $routeAuthorization = new RouteAuthorization($securitySchemeName, null, null);

        $this->assertEquals([], $routeAuthorization->getRequiredScopes());
        $this->assertEquals([], $routeAuthorization->getRequiredPermissions());
    }
}
