<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Security;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Security\AbstractAuthIdentity;
use apivalk\apivalk\Security\Scope;

class AbstractAuthIdentityTest extends TestCase
{
    public function testAbstractAuthIdentity(): void
    {
        $readScope = new Scope('read');
        $writeScope = new Scope('write');

        $identity = new class($readScope, $writeScope) extends AbstractAuthIdentity {
            private $scopes;
            public function __construct($read, $write) { $this->scopes = [$read, $write]; }
            public function getGrantedScopes(): array {
                return $this->scopes;
            }
            public function isAuthenticated(): bool {
                return true;
            }
        };

        $this->assertEquals([$readScope, $writeScope], $identity->getGrantedScopes());
        $this->assertTrue($identity->isAuthenticated());
    }
}
