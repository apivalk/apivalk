<?php

declare(strict_types=1);

namespace Documentation\Response;

use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;
use apivalk\apivalk\Documentation\Response\ErrorObjectPropertyCollection;
use PHPUnit\Framework\TestCase;

class ErrorObjectPropertyCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $collection = new ErrorObjectPropertyCollection(AbstractPropertyCollection::MODE_VIEW);
        $properties = iterator_to_array($collection);

        $this->assertCount(2, $properties);
        $this->assertEquals('errorKey', $properties[0]->getPropertyName());
        $this->assertTrue($properties[0]->isRequired());
        $this->assertEquals('message', $properties[1]->getPropertyName());
        $this->assertTrue($properties[1]->isRequired());
    }
}
