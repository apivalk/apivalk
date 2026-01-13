<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Security;

use apivalk\apivalk\Security\AuthIdentity\UserAuthIdentity;
use apivalk\apivalk\Security\Scope;
use PHPUnit\Framework\TestCase;

class UserAuthIdentityTest extends TestCase
{
    public function testUserAuthIdentity(): void
    {
        $scope = new Scope('read');
        $claims = ['email' => 'test@example.com', 'roles' => ['admin']];
        $identity = new UserAuthIdentity('user-123', [$scope], $claims);

        $this->assertEquals('user-123', $identity->getUserId());
        $this->assertEquals([$scope], $identity->getGrantedScopes());
        $this->assertTrue($identity->isAuthenticated());
        $this->assertEquals($claims, $identity->getClaims());
        $this->assertEquals('test@example.com', $identity->getClaim('email'));
        $this->assertEquals(['admin'], $identity->getClaim('roles'));
        $this->assertNull($identity->getClaim('non-existent'));
    }
}
