<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use apivalk\apivalk\Documentation\Property\FileProperty;
use PHPUnit\Framework\TestCase;

class FilePropertyTest extends TestCase
{
    public function testTypes(): void
    {
        $property = new FileProperty('file', 'The uploaded document');

        $this->assertSame('string', $property->getType());
        $this->assertSame('binary', $property->getFormat());
        $this->assertSame('string', $property->getPhpType());
        $this->assertSame('file', $property->getPropertyName());
        $this->assertSame('The uploaded document', $property->getPropertyDescription());
    }

    public function testConstraintDefaults(): void
    {
        $property = new FileProperty('file');

        $this->assertNull($property->getMaxSizeInBytes());
        $this->assertSame([], $property->getAllowedMediaTypes());
    }

    public function testConstraintsAreFluent(): void
    {
        $property = new FileProperty('file');

        $this->assertSame($property, $property->setMaxSizeInBytes(1024));
        $this->assertSame($property, $property->setAllowedMediaTypes(['application/pdf']));

        $this->assertSame(1024, $property->getMaxSizeInBytes());
        $this->assertSame(['application/pdf'], $property->getAllowedMediaTypes());
    }

    public function testDocumentationArrayIsMinimalByDefault(): void
    {
        $property = new FileProperty('file');

        $this->assertSame(['type' => 'string', 'format' => 'binary'], $property->getDocumentationArray());
    }

    public function testDocumentationArrayCarriesTheConstraints(): void
    {
        $property = new FileProperty('file', 'The uploaded document');
        $property->setMaxSizeInBytes(2048)
            ->setAllowedMediaTypes(['application/pdf'])
            ->setExample('invoice.pdf');

        $this->assertSame(
            [
                'type' => 'string',
                'format' => 'binary',
                'maxLength' => 2048,
                'contentMediaType' => 'application/pdf',
                'description' => 'The uploaded document',
                'example' => 'invoice.pdf',
            ],
            $property->getDocumentationArray()
        );
    }

    public function testDocumentationArrayOmitsTheMediaTypeForSeveralAllowedTypes(): void
    {
        $property = new FileProperty('file');
        $property->setAllowedMediaTypes(['application/pdf', 'application/xml']);

        $this->assertArrayNotHasKey('contentMediaType', $property->getDocumentationArray());
    }
}
