<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\SimpleArrayProperty;

class SimpleArrayPropertyTest extends TestCase
{
    public function testIntArray()
    {
        $property = new SimpleArrayProperty('ids', 'A list of ids', SimpleArrayProperty::TYPE_INT);

        $this->assertSame('array', $property->getType());
        $this->assertSame('int[]', $property->getPhpType());
        $this->assertSame(SimpleArrayProperty::TYPE_INT, $property->getItemType());

        $doc = $property->getDocumentationArray();
        $this->assertSame('array', $doc['type']);
        $this->assertSame(['type' => 'integer'], $doc['items']);
        $this->assertSame('A list of ids', $doc['description']);
    }

    public function testDefaultsToString()
    {
        $property = new SimpleArrayProperty('names');

        $this->assertSame('string', $property->getItemType());
        $this->assertSame('string[]', $property->getPhpType());
        $this->assertSame(['type' => 'string'], $property->getDocumentationArray()['items']);
    }

    public function testNumberArray()
    {
        $property = new SimpleArrayProperty('prices', '', SimpleArrayProperty::TYPE_NUMBER);

        $this->assertSame('float[]', $property->getPhpType());
        $this->assertSame(['type' => 'number'], $property->getDocumentationArray()['items']);
    }

    public function testBoolArray()
    {
        $property = new SimpleArrayProperty('flags', '', SimpleArrayProperty::TYPE_BOOL);

        $this->assertSame('bool[]', $property->getPhpType());
        $this->assertSame(['type' => 'boolean'], $property->getDocumentationArray()['items']);
    }

    public function testDescriptionIsOmittedWhenEmpty()
    {
        $property = new SimpleArrayProperty('ids', '', SimpleArrayProperty::TYPE_INT);

        $this->assertArrayNotHasKey('description', $property->getDocumentationArray());
    }

    public function testExampleIsIncludedWhenSet()
    {
        $property = new SimpleArrayProperty('ids', '', SimpleArrayProperty::TYPE_INT);
        $property->setExample('[23, 41, 22]');

        $this->assertSame('[23, 41, 22]', $property->getDocumentationArray()['example']);
    }

    public function testInvalidItemTypeThrows()
    {
        $this->expectException(\InvalidArgumentException::class);

        new SimpleArrayProperty('ids', '', 'object');
    }
}
