<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\AbstractProperty;

class AbstractPropertyTest extends TestCase
{
    public function testAbstractProperty()
    {
        $property = new class('testProp', 'Description') extends AbstractProperty {
            public function getType(): string
            {
                return 'test';
            }

            public function getPhpType(): string
            {
                return 'string';
            }

            public function getDocumentationArray(): array
            {
                return [];
            }
        };

        $this->assertEquals('testProp', $property->getPropertyName());
        $this->assertEquals('Description', $property->getPropertyDescription());
        $this->assertTrue($property->isRequired());

        $property->setIsRequired(false);
        $this->assertFalse($property->isRequired());

        $this->assertNull($property->getExample());
        $property->setExample('example-value');
        $this->assertEquals('example-value', $property->getExample());

        $this->assertCount(0, $property->getValidators()); // not initialized yet, no validators

        $property->init();

        $this->assertCount(1, $property->getValidators()); // Default validator from factory
    }
}
