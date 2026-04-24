<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use apivalk\apivalk\Documentation\DocBlock\DocBlockResource;
use apivalk\apivalk\Documentation\DocBlock\DocBlockResourceGenerator;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class DocBlockResourceGeneratorTest extends TestCase
{
    public function testGenerateProducesDocBlockWithIdentifierAndAllProperties(): void
    {
        $generator = new DocBlockResourceGenerator();

        $docBlock = $generator->generate(new AnimalResource());

        self::assertInstanceOf(DocBlockResource::class, $docBlock);

        $properties = $docBlock->getProperties();

        self::assertArrayHasKey('animal_uuid', $properties);
        self::assertArrayHasKey('name', $properties);
        self::assertArrayHasKey('type', $properties);
        self::assertArrayHasKey('weight', $properties);
    }

    public function testGeneratedDocBlockRendersExpectedAnnotations(): void
    {
        $generator = new DocBlockResourceGenerator();

        $docBlock = $generator->generate(new AnimalResource());
        $rendered = $docBlock->getResourceDocBlock();

        self::assertStringStartsWith("/**\n", $rendered);
        self::assertStringEndsWith("\n */", $rendered);

        self::assertContains('@property string $animal_uuid', $rendered);
        self::assertContains('@property string $name', $rendered);
        self::assertContains('@property string $type', $rendered);
        self::assertContains('@property float|null $weight', $rendered);
    }

    public function testEmptyResourceProducesPlaceholder(): void
    {
        $docBlock = new DocBlockResource();

        $rendered = $docBlock->getResourceDocBlock();

        self::assertContains('(no properties)', $rendered);
    }

    public function testPropertyDescriptionIsAppendedToAnnotation(): void
    {
        $generator = new DocBlockResourceGenerator();

        $docBlock = $generator->generate(new AnimalResource());
        $rendered = $docBlock->getResourceDocBlock();

        self::assertContains('Unique identifier of the animal', $rendered);
        self::assertContains('Name of the animal', $rendered);
    }
}
