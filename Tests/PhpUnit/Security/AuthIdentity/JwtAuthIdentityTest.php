<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Security\AuthIdentity;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Security\AuthIdentity\JwtAuthIdentity;

class JwtAuthIdentityTest extends TestCase
{
    public function testGetters(): void
    {
        $identity = new JwtAuthIdentity(
            'user123',
            'user@example.com',
            'sub-456',
            ['read', 'write'],
            ['perm1', 'perm2'],
            ['dev-client', 'mobile-client']
        );

        $this->assertEquals('user123', $identity->getUsername());
        $this->assertEquals('user@example.com', $identity->getEmail());
        $this->assertEquals('sub-456', $identity->getSub());
        $this->assertEquals(['read', 'write'], $identity->getScopes());
        $this->assertEquals(['perm1', 'perm2'], $identity->getPermissions());
        $this->assertEquals(['dev-client', 'mobile-client'], $identity->getAud());
        $this->assertTrue($identity->isAudGranted('dev-client'));
        $this->assertTrue($identity->isAudGranted('mobile-client'));
        $this->assertFalse($identity->isAudGranted('staging-client'));
        $this->assertTrue($identity->isAuthenticated());
    }

    public function testNullValues(): void
    {
        $identity = new JwtAuthIdentity(null, null, null, [], []);

        $this->assertNull($identity->getUsername());
        $this->assertNull($identity->getEmail());
        $this->assertNull($identity->getSub());
        $this->assertEmpty($identity->getScopes());
        $this->assertEmpty($identity->getPermissions());
        $this->assertSame([], $identity->getAud());
        $this->assertTrue($identity->isAuthenticated());
    }
}
