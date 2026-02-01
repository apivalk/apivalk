<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;
use PHPUnit\Framework\TestCase;

class SecurityRequirementObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $security = new SecurityRequirementObject('BearerAuth', ['read', 'write']);
        
        $expected = [
            'BearerAuth' => ['read', 'write']
        ];

        $this->assertEquals($expected, $security->toArray());
        $this->assertEquals('BearerAuth', $security->getSecuritySchemeName());
        $this->assertCount(2, $security->getScopes());
        $this->assertEquals('read', $security->getScopes()[0]);
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
        $this->assertNull($security->getSecuritySchemeName());
        $this->assertEquals([], $security->toArray());
    }
}
