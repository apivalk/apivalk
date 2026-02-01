<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Security;

use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use PHPUnit\Framework\TestCase;

class AbstractAuthIdentityTest extends TestCase
{
    public function testAbstractAuthIdentity(): void
    {
        $identity = new class(['read', 'write'], ['perm1']) extends AbstractAuthIdentity {
            private $scopes;
            private $perms;
            public function __construct(array $scopes, array $perms) {
                $this->scopes = $scopes;
                $this->perms = $perms;
            }
            public function getScopes(): array {
                return $this->scopes;
            }
            public function getPermissions(): array {
                return $this->perms;
            }
            public function isAuthenticated(): bool {
                return true;
            }
        };

        $this->assertEquals(['read', 'write'], $identity->getScopes());
        $this->assertEquals(['perm1'], $identity->getPermissions());
        $this->assertTrue($identity->isAuthenticated());
        $this->assertTrue($identity->isScopeGranted('read'));
        $this->assertFalse($identity->isScopeGranted('other'));
        $this->assertTrue($identity->isPermissionGranted('perm1'));
    }
}
