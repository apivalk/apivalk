<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Security;

use apivalk\apivalk\Security\Scope;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    public function testScope(): void
    {
        $scope = new Scope('read:orders', 'Allows reading orders');

        $this->assertEquals('read:orders', $scope->getName());
        $this->assertEquals('Allows reading orders', $scope->getDescription());
        $this->assertEquals('read:orders', (string)$scope);
    }

    public function testScopeWithNullDescription(): void
    {
        $scope = new Scope('write');

        $this->assertEquals('write', $scope->getName());
        $this->assertNull($scope->getDescription());
    }
}
