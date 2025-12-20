<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\LicenseObject;

class LicenseObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $license = new LicenseObject('MIT', 'MIT', 'https://opensource.org/licenses/MIT');
        
        $expected = [
            'name' => 'MIT',
            'identifier' => 'MIT',
            'url' => 'https://opensource.org/licenses/MIT'
        ];

        $this->assertEquals($expected, $license->toArray());
        $this->assertEquals('MIT', $license->getName());
        $this->assertEquals('MIT', $license->getIdentifier());
        $this->assertEquals('https://opensource.org/licenses/MIT', $license->getUrl());
    }
}
