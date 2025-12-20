<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;

class ArrayPropertyTest extends TestCase
{
    public function testArrayProperty()
    {
        $objectProperty = new class('obj', 'desc') extends AbstractObjectProperty {
            public function getPropertyCollection(): \apivalk\apivalk\Documentation\Property\AbstractPropertyCollection {
                return new class(\apivalk\apivalk\Documentation\Property\AbstractPropertyCollection::MODE_VIEW) extends \apivalk\apivalk\Documentation\Property\AbstractPropertyCollection {
                    public function __construct($mode) {}
                };
            }
        };

        $arrayProperty = new ArrayProperty('list', 'A list', $objectProperty);
        $this->assertEquals('array', $arrayProperty->getType());
        
        $arrayDoc = $arrayProperty->getDocumentationArray();
        $this->assertEquals('array', $arrayDoc['type']);
        $this->assertEquals($objectProperty->getDocumentationArray(), $arrayDoc['items']);
        $this->assertEquals('A list', $arrayDoc['description']);
    }
}
