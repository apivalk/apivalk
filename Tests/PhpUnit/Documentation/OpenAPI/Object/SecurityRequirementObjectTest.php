<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use apivalk\apivalk\Security\Scope;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;

class SecurityRequirementObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $security = new SecurityRequirementObject('BearerAuth', [new Scope('read'), new Scope('write')]);
        
        $expected = [
            'BearerAuth' => ['read', 'write']
        ];

        $this->assertEquals($expected, $security->toArray());
        $this->assertEquals('BearerAuth', $security->getName());
        $this->assertCount(2, $security->getScopes());
        $this->assertEquals('read', $security->getScopes()[0]->getName());
    }

    public function testToArrayDefaultScopes(): void
    {
        $security = new SecurityRequirementObject('BearerAuth');
        $this->assertEquals(['BearerAuth' => []], $security->toArray());
    }

    public function testPublicEndpoint(): void
    {
        $security = new SecurityRequirementObject();
        $this->assertTrue($security->isPublicEndpoint());
        $this->assertNull($security->getName());
        $this->assertEquals([], $security->toArray());
    }
}
