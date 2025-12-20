<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Generator\MediaTypeGenerator;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;

class MediaTypeGeneratorTest extends TestCase
{
    public function testMediaTypeGenerator(): void
    {
        $generator = new MediaTypeGenerator();
        $schema = $this->createMock(SchemaObject::class);
        $mediaType = $generator->generate('application/json', $schema);
        
        $this->assertEquals('application/json', $mediaType->getMediaType());
        $this->assertSame($schema, $mediaType->getSchema());
    }
}
