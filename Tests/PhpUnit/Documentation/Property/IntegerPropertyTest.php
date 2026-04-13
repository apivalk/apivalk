<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\IntegerProperty;

class IntegerPropertyTest extends TestCase
{
    public function testIntegerProperty(): void
    {
        $property = new IntegerProperty('age', 'User Age', IntegerProperty::FORMAT_INT32);
        $this->assertEquals('integer', $property->getType());
        $this->assertEquals('int', $property->getPhpType());
        $this->assertEquals('int32', $property->getFormat());

        $property->setFormat(IntegerProperty::FORMAT_INT64);
        $this->assertEquals('int64', $property->getFormat());

        $property->setMinimumValue(0)
                 ->setMaximumValue(100)
                 ->setIsExclusiveMinimum(true)
                 ->setIsExclusiveMaximum(false);

        $doc = $property->getDocumentationArray();
        $this->assertEquals('integer', $doc['type']);
        $this->assertEquals('int64', $doc['format']);
        $this->assertEquals(0, $doc['minimum']);
        $this->assertEquals(100, $doc['maximum']);
        $this->assertTrue($doc['exclusiveMinimum']);
        $this->assertFalse($doc['exclusiveMaximum']);
    }

    public function testInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new IntegerProperty('test', '', 'float');
    }
}
