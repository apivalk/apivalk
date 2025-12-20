<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;
use apivalk\apivalk\Documentation\Property\StringProperty;

class AbstractObjectPropertyTest extends TestCase
{
    public function testAbstractObjectProperty()
    {
        $collection = new class(AbstractPropertyCollection::MODE_VIEW) extends AbstractPropertyCollection {
            public function __construct(string $mode)
            {
                $this->addProperty(new StringProperty('subProp', 'Sub Property'));
            }
        };

        $objectProperty = new class('obj', 'Object Description', $collection) extends AbstractObjectProperty {
            private $coll;
            public function __construct($name, $desc, $coll) {
                parent::__construct($name, $desc);
                $this->coll = $coll;
            }
            public function getPropertyCollection(): AbstractPropertyCollection {
                return $this->coll;
            }
        };

        $this->assertEquals('object', $objectProperty->getType());
        $doc = $objectProperty->getDocumentationArray();
        $this->assertEquals('object', $doc['type']);
        $this->assertArrayHasKey('subProp', $doc['properties']);
        $this->assertEquals(['subProp'], $doc['required']);
        $this->assertEquals('Object Description', $doc['description']);
    }
}
