<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\MediaTypeObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;

class MediaTypeObjectTest extends TestCase
{
    public function testMediaTypeObjectToArray(): void
    {
        $schema = $this->createMock(SchemaObject::class);
        $schema->method('toArray')->willReturn(['type' => 'object']);

        $mediaType = new MediaTypeObject($schema, 'application/json');
        
        $expected = [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                ]
            ]
        ];

        $this->assertEquals($expected, $mediaType->toArray());
        $this->assertSame($schema, $mediaType->getSchema());
        $this->assertEquals('application/json', $mediaType->getMediaType());
    }
}
