<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\ComponentsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;

class ComponentsObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $components = new ComponentsObject();
        $schema = new SchemaObject('object');
        $components->setSchemas(['User' => $schema]);

        $result = $components->toArray();

        $this->assertArrayHasKey('schemas', $result);
        $this->assertArrayHasKey('User', $result['schemas']);
    }
}
