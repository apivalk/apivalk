<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\DocBlock\DocBlockShape;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Documentation\Property\NumberProperty;

class DocBlockShapeTest extends TestCase
{
    public function testGetClassName(): void
    {
        $shape = new DocBlockShape('MyRequest', 'Body');
        $this->assertEquals('MyRequestBodyShape', $shape->getClassName());
    }

    public function testGetClassNameInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $shape = new DocBlockShape('123Invalid', 'Body');
        $shape->getClassName();
    }

    public function testToStringWithProperties(): void
    {
        $shape = new DocBlockShape('User', 'Query');

        $prop1 = new StringProperty('name');
        $prop1->setIsRequired(true);

        $prop2 = new NumberProperty('age');
        $prop2->setFormat(NumberProperty::FORMAT_INT32);
        $prop2->setIsRequired(false);

        $shape->addProperty($prop1);
        $shape->addProperty($prop2);

        $result = $shape->toString('App\\Api\\Shape');

        $this->assertStringContainsString('namespace App\\Api\\Shape;', $result);
        $this->assertStringContainsString('interface UserQueryShape', $result);
        $this->assertStringContainsString('@property-read string $name', $result);
        $this->assertStringContainsString('@property-read int|null $age', $result);
    }

    public function testToStringEmpty(): void
    {
        $shape = new DocBlockShape('User', 'Body');
        $result = $shape->toString('App\\Api\\Shape');

        $this->assertStringContainsString('* (empty shape)', $result);
    }

    public function testToStringInvalidNamespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $shape = new DocBlockShape('User', 'Body');
        $shape->toString('Invalid-Namespace');
    }

    public function testToStringWithNamespacedPhpType(): void
    {
        $shape = new DocBlockShape('User', 'Body');

        // We need a property that returns a namespaced PHP type.
        // StringProperty returns \DateTime if format is date or date-time.
        $prop = new StringProperty('createdAt');
        $prop->setFormat(StringProperty::FORMAT_DATE);
        $prop->setIsRequired(true);

        $shape->addProperty($prop);

        $result = $shape->toString('App\\Api\\Shape');

        $this->assertStringContainsString('@property-read \\DateTime $createdAt', $result);
    }
}
