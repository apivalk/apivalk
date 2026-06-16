<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;
use apivalk\apivalk\Documentation\Property\SimpleArrayProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Router\Route\Pagination\Pagination;

class SchemaObjectTest extends TestCase
{
    public function testSchemaObjectToArray(): void
    {
        $prop = new StringProperty('name', 'User name');
        $prop->setIsRequired(true);

        $schema = new SchemaObject('object', true, [$prop], Pagination::page());
        
        $result = $schema->toArray();

        $this->assertEquals('object', $result['type']);
        $this->assertEquals(['name'], $result['required']);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('pagination', $result['properties']);
        $this->assertEquals('string', $result['properties']['name']['type']);
        
        $this->assertEquals('object', $schema->getType());
        $this->assertTrue($schema->isRequired());
        $this->assertCount(1, $schema->getProperties());
        $this->assertNotNull($schema->getPagination());
    }

    public function testSchemaObjectRendersSimpleArrayPropertyItems(): void
    {
        $required = new SimpleArrayProperty('ids', 'Selected IDs', SimpleArrayProperty::TYPE_INT);
        $optional = (new SimpleArrayProperty('tags', 'Labels', SimpleArrayProperty::TYPE_STRING))
            ->setIsRequired(false);

        $schema = new SchemaObject('object', true, [$required, $optional]);

        $result = $schema->toArray();

        $this->assertSame(
            ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Selected IDs'],
            $result['properties']['ids']
        );
        $this->assertSame(
            ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Labels'],
            $result['properties']['tags']
        );

        $this->assertSame(['ids'], $result['required']);
    }
}
