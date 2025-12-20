<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;
use apivalk\apivalk\Documentation\Property\StringProperty;

class SchemaObjectTest extends TestCase
{
    public function testSchemaObjectToArray(): void
    {
        $prop = new StringProperty('name', 'User name');
        $prop->setIsRequired(true);

        $schema = new SchemaObject('object', true, [$prop], true);
        
        $result = $schema->toArray();

        $this->assertEquals('object', $result['type']);
        $this->assertEquals(['name'], $result['required']);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('pagination', $result['properties']);
        $this->assertEquals('string', $result['properties']['name']['type']);
        
        $this->assertEquals('object', $schema->getType());
        $this->assertTrue($schema->isRequired());
        $this->assertCount(1, $schema->getProperties());
        $this->assertTrue($schema->hasPagination());
    }
}
