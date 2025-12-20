<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\SingleSchemaObject;

class SingleSchemaObjectTest extends TestCase
{
    public function testSingleSchemaObjectToArray(): void
    {
        $singleSchema = new SingleSchemaObject('id', 'integer', true);
        
        $expected = [
            'type' => 'integer',
            'required' => ['id']
        ];

        $this->assertEquals($expected, $singleSchema->toArray());
        $this->assertEquals('id', $singleSchema->getPropertyName());
        $this->assertEquals('integer', $singleSchema->getType());
        $this->assertTrue($singleSchema->isRequired());
    }

    public function testSingleSchemaObjectNotRequired(): void
    {
        $singleSchema = new SingleSchemaObject('id', 'integer', false);
        
        $expected = [
            'type' => 'integer'
            // 'required' is empty array, so array_filter removes it
        ];

        $this->assertEquals($expected, $singleSchema->toArray());
        $this->assertFalse($singleSchema->isRequired());
    }
}
