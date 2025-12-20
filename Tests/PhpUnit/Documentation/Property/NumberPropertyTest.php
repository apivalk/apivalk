<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\NumberProperty;

class NumberPropertyTest extends TestCase
{
    public function testNumberProperty()
    {
        $property = new NumberProperty('age', 'User Age', NumberProperty::FORMAT_INT32);
        $this->assertEquals('integer', $property->getType());
        $this->assertEquals('int', $property->getPhpType());

        $property->setFormat(NumberProperty::FORMAT_FLOAT);
        $this->assertEquals('number', $property->getType());
        $this->assertEquals('float', $property->getPhpType());

        $property->setMinimumValue(0)
                 ->setMaximumValue(100)
                 ->setIsExclusiveMinimum(true)
                 ->setIsExclusiveMaximum(false);

        $doc = $property->getDocumentationArray();
        $this->assertEquals('number', $doc['type']);
        $this->assertEquals('float', $doc['format']);
        $this->assertEquals(0, $doc['minimum']);
        $this->assertEquals(100, $doc['maximum']);
        $this->assertTrue($doc['exclusiveMinimum']);
        $this->assertFalse($doc['exclusiveMaximum']);
    }
}
