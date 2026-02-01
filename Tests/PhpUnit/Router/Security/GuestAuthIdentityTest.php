<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Security;

use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use PHPUnit\Framework\TestCase;

class GuestAuthIdentityTest extends TestCase
{
    public function testGuestAuthIdentity(): void
    {
        $identity = new GuestAuthIdentity(['public-read']);

        $this->assertEquals(['public-read'], $identity->getScopes());
        $this->assertFalse($identity->isAuthenticated());
    }

    public function testGuestAuthIdentityEmptyScopes(): void
    {
        $identity = new GuestAuthIdentity();

        $this->assertEquals([], $identity->getScopes());
        $this->assertFalse($identity->isAuthenticated());
    }
}
