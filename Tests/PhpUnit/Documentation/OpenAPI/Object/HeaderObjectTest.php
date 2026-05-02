<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\HeaderObject;

class HeaderObjectTest extends TestCase
{
    public function testHeaderObjectToArray(): void
    {
        $header = new HeaderObject('Header description', true);

        $expected = [
            'description' => 'Header description',
            'required' => true,
        ];

        $this->assertEquals($expected, $header->toArray());
        $this->assertEquals('Header description', $header->getDescription());
        $this->assertTrue($header->isRequired());
        $this->assertNull($header->getSchema());
    }

    public function testHeaderObjectToArrayWithSchema(): void
    {
        $schema = ['type' => 'string', 'example' => 'en'];
        $header = new HeaderObject('Header description', false, $schema);

        $expected = [
            'description' => 'Header description',
            'required' => false,
            'schema' => ['type' => 'string', 'example' => 'en'],
        ];

        $this->assertEquals($expected, $header->toArray());
        $this->assertEquals($schema, $header->getSchema());
    }
}
