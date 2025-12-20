<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;

class SecurityRequirementObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $security = new SecurityRequirementObject('BearerAuth', ['read', 'write']);
        
        $expected = [
            'BearerAuth' => ['read', 'write']
        ];

        $this->assertEquals($expected, $security->toArray());
        $this->assertEquals('BearerAuth', $security->getName());
        $this->assertEquals(['read', 'write'], $security->getScopes());
    }

    public function testToArrayDefaultScopes(): void
    {
        $security = new SecurityRequirementObject('BearerAuth');
        $this->assertEquals(['BearerAuth' => []], $security->toArray());
    }
}
