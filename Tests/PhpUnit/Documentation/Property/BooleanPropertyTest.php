<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\BooleanProperty;

class BooleanPropertyTest extends TestCase
{
    public function testBooleanProperty()
    {
        $property = new BooleanProperty('active', 'Is Active', true);
        $this->assertEquals('boolean', $property->getType());
        $this->assertEquals('bool', $property->getPhpType());
        $this->assertTrue($property->getDefault());

        $doc = $property->getDocumentationArray();
        $this->assertEquals('boolean', $doc['type']);
        $this->assertTrue($doc['default']);
        $this->assertEquals('Is Active', $doc['description']);
    }
}
