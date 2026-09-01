<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Router\Route\Filter\BooleanFilter;
use apivalk\apivalk\Router\Route\Filter\Operator;
use PHPUnit\Framework\TestCase;

class BooleanFilterTest extends TestCase
{
    private function property(string $name = 'field'): BooleanProperty
    {
        return new BooleanProperty($name, 'Description', false);
    }

    public function testSupportedOperators(): void
    {
        $this->assertSame([Operator::EQ, Operator::NULL], BooleanFilter::supportedOperators());
    }

    public function testDeclaredOperatorsAreKeptInOrder(): void
    {
        $filter = new BooleanFilter($this->property(), Operator::NULL, Operator::EQ);

        $this->assertSame([Operator::NULL, Operator::EQ], $filter->getAllowedOperators());
        $this->assertSame(Operator::NULL, $filter->getDefaultOperator());
        $this->assertTrue($filter->allows(Operator::EQ));
    }

    public function testRejectsAnEmptyOperatorList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('declares no operator');

        new BooleanFilter($this->property());
    }

    public function testRejectsAnUnsupportedOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported by');

        new BooleanFilter($this->property(), Operator::IN);
    }

    public function testRejectsADuplicateOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate operator');

        new BooleanFilter($this->property(), Operator::EQ, Operator::EQ);
    }

    public function testConditionRoundTrip(): void
    {
        $filter = new BooleanFilter($this->property('created'), Operator::EQ);

        $this->assertFalse($filter->has(Operator::EQ));
        $this->assertNull($filter->raw(Operator::EQ));

        $filter->setCondition(Operator::EQ, true, 'true');

        $this->assertTrue($filter->has(Operator::EQ));
        $this->assertSame(true, $filter->equal);
        $this->assertSame('true', $filter->raw(Operator::EQ));
        $this->assertSame('created', $filter->getField());
    }

    public function testSetConditionRejectsAnUndeclaredOperator(): void
    {
        $filter = new BooleanFilter($this->property(), Operator::EQ);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not allowed on field');

        $filter->setCondition(Operator::NULL, true, 'true');
    }

    public function testReadingAnUndeclaredOperatorThrows(): void
    {
        $filter = new BooleanFilter($this->property(), Operator::EQ);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('is not declared on field');

        $filter->isNull;
    }

    public function testReadingAnUnknownAccessorThrows(): void
    {
        $filter = new BooleanFilter($this->property(), Operator::EQ);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown filter accessor');

        $filter->nonsense;
    }

    public function testIssetReflectsTheDeclaredOperators(): void
    {
        $filter = new BooleanFilter($this->property(), Operator::EQ);

        $this->assertTrue(isset($filter->equal));
        $this->assertFalse(isset($filter->isNull));
    }

    public function testGetPropertyKeepsTheDeclaredProperty(): void
    {
        $property = $this->property();
        $filter = new BooleanFilter($property, Operator::EQ);

        $this->assertSame($property, $filter->getProperty());
    }
}
