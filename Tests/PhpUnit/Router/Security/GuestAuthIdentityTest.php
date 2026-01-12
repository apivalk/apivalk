<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Security;

use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use apivalk\apivalk\Security\Scope;
use PHPUnit\Framework\TestCase;

class GuestAuthIdentityTest extends TestCase
{
    public function testGuestAuthIdentity(): void
    {
        $scope = new Scope('public-read');
        $identity = new GuestAuthIdentity([$scope]);

        $this->assertEquals([$scope], $identity->getGrantedScopes());
        $this->assertFalse($identity->isAuthenticated());
    }

    public function testGuestAuthIdentityEmptyScopes(): void
    {
        $identity = new GuestAuthIdentity();

        $this->assertEquals([], $identity->getGrantedScopes());
        $this->assertFalse($identity->isAuthenticated());
    }
}
