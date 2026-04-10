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
            ['perm1', 'perm2']
        );

        $this->assertEquals('user123', $identity->getUsername());
        $this->assertEquals('user@example.com', $identity->getEmail());
        $this->assertEquals('sub-456', $identity->getSub());
        $this->assertEquals(['read', 'write'], $identity->getScopes());
        $this->assertEquals(['perm1', 'perm2'], $identity->getPermissions());
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
        $this->assertTrue($identity->isAuthenticated());
    }
}
