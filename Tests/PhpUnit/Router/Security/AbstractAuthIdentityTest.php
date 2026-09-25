<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Security;

use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use PHPUnit\Framework\TestCase;

class AbstractAuthIdentityTest extends TestCase
{
    public function testAbstractAuthIdentity(): void
    {
        $identity = $this->identity(['read', 'write'], ['perm1'], ['dev-client']);

        $this->assertEquals(['read', 'write'], $identity->getScopes());
        $this->assertEquals(['perm1'], $identity->getPermissions());
        $this->assertEquals(['dev-client'], $identity->getAud());
        $this->assertTrue($identity->isAuthenticated());
        $this->assertTrue($identity->isScopeGranted('read'));
        $this->assertFalse($identity->isScopeGranted('other'));
        $this->assertTrue($identity->isPermissionGranted('perm1'));
    }

    public function testAudDefaultsToAnEmptyList(): void
    {
        $identity = $this->identity([], [], []);

        $this->assertSame([], $identity->getAud());
    }

    public function testIsAnyAudGranted_whenOneOfSeveralAudiencesMatches(): void
    {
        $identity = $this->identity([], [], ['dev-client']);

        $this->assertTrue($identity->isAnyAudGranted(['staging-client', 'dev-client']));
    }

    public function testIsAnyAudGranted_whenNoAudienceMatches(): void
    {
        $identity = $this->identity([], [], ['dev-client']);

        $this->assertFalse($identity->isAnyAudGranted(['staging-client']));
    }

    public function testIsAnyAudGranted_withAnEmptyListOnEitherSide(): void
    {
        $this->assertFalse($this->identity([], [], ['dev-client'])->isAnyAudGranted([]));
        $this->assertFalse($this->identity([], [], [])->isAnyAudGranted(['dev-client']));
    }

    /**
     * @param string[] $scopes
     * @param string[] $permissions
     * @param string[] $aud
     */
    private function identity(array $scopes, array $permissions, array $aud): AbstractAuthIdentity
    {
        return new class($scopes, $permissions, $aud) extends AbstractAuthIdentity {
            private array $scopes;
            private array $permissions;

            public function __construct(array $scopes, array $permissions, array $aud)
            {
                $this->scopes = $scopes;
                $this->permissions = $permissions;
                $this->aud = $aud;
            }

            public function getScopes(): array
            {
                return $this->scopes;
            }

            public function getPermissions(): array
            {
                return $this->permissions;
            }

            public function isAuthenticated(): bool
            {
                return true;
            }
        };
    }
}
