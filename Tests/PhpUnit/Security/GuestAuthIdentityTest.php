<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Security;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Security\GuestAuthIdentity;
use apivalk\apivalk\Security\Scope;

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
