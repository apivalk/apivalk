<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Router\Route\Filter\EnumFilter;
use apivalk\apivalk\Router\Route\Filter\Operator;
use PHPUnit\Framework\TestCase;

class EnumFilterTest extends TestCase
{
    private function property(string $name = 'field'): EnumProperty
    {
        return new EnumProperty($name, 'Description', ['draft', 'active']);
    }

    public function testSupportedOperators(): void
    {
        $this->assertSame([Operator::EQ, Operator::NEQ, Operator::IN, Operator::NULL], EnumFilter::supportedOperators());
    }

    public function testDeclaredOperatorsAreKeptInOrder(): void
    {
        $filter = new EnumFilter($this->property(), Operator::NEQ, Operator::EQ);

        $this->assertSame([Operator::NEQ, Operator::EQ], $filter->getAllowedOperators());
        $this->assertSame(Operator::NEQ, $filter->getDefaultOperator());
        $this->assertTrue($filter->allows(Operator::EQ));
    }

    public function testRejectsAnEmptyOperatorList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('declares no operator');

        new EnumFilter($this->property());
    }

    public function testRejectsAnUnsupportedOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported by');

        new EnumFilter($this->property(), Operator::LIKE);
    }

    public function testRejectsADuplicateOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate operator');

        new EnumFilter($this->property(), Operator::EQ, Operator::EQ);
    }

    public function testConditionRoundTrip(): void
    {
        $filter = new EnumFilter($this->property('created'), Operator::EQ);

        $this->assertFalse($filter->has(Operator::EQ));
        $this->assertNull($filter->raw(Operator::EQ));

        $filter->setCondition(Operator::EQ, 'active', 'active');

        $this->assertTrue($filter->has(Operator::EQ));
        $this->assertSame('active', $filter->equal);
        $this->assertSame('active', $filter->raw(Operator::EQ));
        $this->assertSame('created', $filter->getField());
    }

    public function testSetConditionRejectsAnUndeclaredOperator(): void
    {
        $filter = new EnumFilter($this->property(), Operator::EQ);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not allowed on field');

        $filter->setCondition(Operator::NEQ, 'active', 'active');
    }

    public function testReadingAnUndeclaredOperatorThrows(): void
    {
        $filter = new EnumFilter($this->property(), Operator::EQ);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('is not declared on field');

        $filter->notEqual;
    }

    public function testReadingAnUnknownAccessorThrows(): void
    {
        $filter = new EnumFilter($this->property(), Operator::EQ);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown filter accessor');

        $filter->nonsense;
    }

    public function testIssetReflectsTheDeclaredOperators(): void
    {
        $filter = new EnumFilter($this->property(), Operator::EQ);

        $this->assertTrue(isset($filter->equal));
        $this->assertFalse(isset($filter->notEqual));
    }

    public function testGetPropertyKeepsTheDeclaredProperty(): void
    {
        $property = $this->property();
        $filter = new EnumFilter($property, Operator::EQ);

        $this->assertSame($property, $filter->getProperty());
    }

    public function testInIsAList(): void
    {
        $filter = new EnumFilter($this->property(), Operator::IN);
        $this->assertSame([], $filter->in);

        $filter->setCondition(Operator::IN, ['active'], 'active');
        $this->assertSame(['active'], $filter->in);
    }
}
