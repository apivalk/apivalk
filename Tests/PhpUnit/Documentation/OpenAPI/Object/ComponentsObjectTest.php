<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\ComponentsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecuritySchemeObject;

class ComponentsObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $components = new ComponentsObject();
        $schema = new SchemaObject('object');
        $components->setSchemas(['User' => $schema]);
        
        $securityScheme = new SecuritySchemeObject(
            'http',
            'bearerAuth',
            null,
            null,
            'bearer',
            'JWT',
            null,
            null
        );
        $components->setSecuritySchemes([$securityScheme]);

        $result = $components->toArray();

        $this->assertArrayHasKey('schemas', $result);
        $this->assertArrayHasKey('User', $result['schemas']);
        $this->assertArrayHasKey('securitySchemes', $result);
        $this->assertArrayHasKey('bearerAuth', $result['securitySchemes']);
    }
}
