<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Response\ErrorApivalkObjectPropertyCollection;
use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;

class ErrorApivalkObjectPropertyCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $collection = new ErrorApivalkObjectPropertyCollection(AbstractPropertyCollection::MODE_VIEW);
        $properties = iterator_to_array($collection);
        
        $this->assertCount(2, $properties);
        $this->assertEquals('name', $properties[0]->getPropertyName());
        $this->assertEquals('error', $properties[1]->getPropertyName());
    }
}
