<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\FloatProperty;

class FloatPropertyTest extends TestCase
{
    public function testFloatProperty(): void
    {
        $property = new FloatProperty('price', 'Product Price', FloatProperty::FORMAT_FLOAT);
        $this->assertEquals('number', $property->getType());
        $this->assertEquals('float', $property->getPhpType());
        $this->assertEquals('float', $property->getFormat());

        $property->setFormat(FloatProperty::FORMAT_DOUBLE);
        $this->assertEquals('double', $property->getFormat());

        $property->setMinimumValue(0)
                 ->setMaximumValue(100.5)
                 ->setIsExclusiveMinimum(true)
                 ->setIsExclusiveMaximum(false);

        $doc = $property->getDocumentationArray();
        $this->assertEquals('number', $doc['type']);
        $this->assertEquals('double', $doc['format']);
        $this->assertEquals(0.0, $doc['minimum']);
        $this->assertEquals(100.5, $doc['maximum']);
        $this->assertTrue($doc['exclusiveMinimum']);
        $this->assertFalse($doc['exclusiveMaximum']);
    }

    public function testInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FloatProperty('test', '', 'int32');
    }
}
