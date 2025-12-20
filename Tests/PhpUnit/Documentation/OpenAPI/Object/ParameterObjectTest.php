<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\ParameterObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SingleSchemaObject;

class ParameterObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $schema = new SingleSchemaObject('id', 'integer');
        $parameter = new ParameterObject('id', 'path', 'User ID', true, $schema);
        
        $expected = [
            'name' => 'id',
            'in' => 'path',
            'description' => 'User ID',
            'required' => true,
            'schema' => [
                'type' => 'integer',
                'required' => ['id']
            ]
        ];

        $this->assertEquals($expected, $parameter->toArray());
    }

    public function testToArrayMinimal(): void
    {
        $parameter = new ParameterObject('id', 'query');
        
        $expected = [
            'name' => 'id',
            'in' => 'query',
            'description' => null,
            'required' => true,
            'schema' => null
        ];

        $this->assertEquals($expected, $parameter->toArray());
    }
}
