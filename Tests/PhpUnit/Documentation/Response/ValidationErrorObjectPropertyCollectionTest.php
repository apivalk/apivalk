<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Response\ValidationErrorObjectPropertyCollection;
use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;

class ValidationErrorObjectPropertyCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $collection = new ValidationErrorObjectPropertyCollection(AbstractPropertyCollection::MODE_VIEW);
        $properties = iterator_to_array($collection);

        $this->assertCount(3, $properties);
        $this->assertEquals('errorKey', $properties[0]->getPropertyName());
        $this->assertTrue($properties[0]->isRequired());
        $this->assertEquals('message', $properties[1]->getPropertyName());
        $this->assertTrue($properties[1]->isRequired());
        $this->assertEquals('parameter', $properties[2]->getPropertyName());
        $this->assertTrue($properties[2]->isRequired());
    }
}
