<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\StringProperty;

class StringPropertyTest extends TestCase
{
    public function testStringProperty()
    {
        $property = new StringProperty('name', 'User Name');
        $this->assertEquals('string', $property->getType());
        $this->assertEquals('string', $property->getPhpType());

        $property->setFormat(StringProperty::FORMAT_DATE);
        $this->assertEquals('\DateTime', $property->getPhpType());

        $property->setDefault('default-val')
                 ->setMinLength(2)
                 ->setMaxLength(10)
                 ->setPattern('/^[a-z]+$/')
                 ->setEnums(['a', 'b']);

        $doc = $property->getDocumentationArray();
        $this->assertEquals('string', $doc['type']);
        $this->assertEquals('default-val', $doc['default']);
        $this->assertEquals(2, $doc['minLength']);
        $this->assertEquals(10, $doc['maxLength']);
        $this->assertEquals('/^[a-z]+$/', $doc['pattern']);
        $this->assertEquals(['a', 'b'], $doc['enum']);
        $this->assertEquals('User Name', $doc['description']);
    }

    public function testStringPropertyInvalidFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        $property = new StringProperty('test');
        $property->setFormat('invalid');
    }
}
