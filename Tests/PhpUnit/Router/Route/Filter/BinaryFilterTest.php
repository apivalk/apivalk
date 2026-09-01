<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Router\Route\Filter\BinaryFilter;
use apivalk\apivalk\Router\Route\Filter\Operator;
use PHPUnit\Framework\TestCase;

class BinaryFilterTest extends TestCase
{
    private function property(string $name = 'field'): BinaryProperty
    {
        return new BinaryProperty($name, 'Description');
    }

    public function testSupportedOperators(): void
    {
        $this->assertSame([Operator::EQ, Operator::NEQ, Operator::IN, Operator::NULL], BinaryFilter::supportedOperators());
    }

    public function testDeclaredOperatorsAreKeptInOrder(): void
    {
        $filter = new BinaryFilter($this->property(), Operator::NEQ, Operator::EQ);

        $this->assertSame([Operator::NEQ, Operator::EQ], $filter->getAllowedOperators());
        $this->assertSame(Operator::NEQ, $filter->getDefaultOperator());
        $this->assertTrue($filter->allows(Operator::EQ));
    }

    public function testRejectsAnEmptyOperatorList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('declares no operator');

        new BinaryFilter($this->property());
    }

    public function testRejectsAnUnsupportedOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported by');

        new BinaryFilter($this->property(), Operator::GT);
    }

    public function testRejectsADuplicateOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate operator');

        new BinaryFilter($this->property(), Operator::EQ, Operator::EQ);
    }

    public function testConditionRoundTrip(): void
    {
        $filter = new BinaryFilter($this->property('created'), Operator::EQ);

        $this->assertFalse($filter->has(Operator::EQ));
        $this->assertNull($filter->raw(Operator::EQ));

        $filter->setCondition(Operator::EQ, '0101', '0101');

        $this->assertTrue($filter->has(Operator::EQ));
        $this->assertSame('0101', $filter->equal);
        $this->assertSame('0101', $filter->raw(Operator::EQ));
        $this->assertSame('created', $filter->getField());
    }

    public function testSetConditionRejectsAnUndeclaredOperator(): void
    {
        $filter = new BinaryFilter($this->property(), Operator::EQ);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not allowed on field');

        $filter->setCondition(Operator::NEQ, '0101', '0101');
    }

    public function testReadingAnUndeclaredOperatorThrows(): void
    {
        $filter = new BinaryFilter($this->property(), Operator::EQ);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('is not declared on field');

        $filter->notEqual;
    }

    public function testReadingAnUnknownAccessorThrows(): void
    {
        $filter = new BinaryFilter($this->property(), Operator::EQ);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown filter accessor');

        $filter->nonsense;
    }

    public function testIssetReflectsTheDeclaredOperators(): void
    {
        $filter = new BinaryFilter($this->property(), Operator::EQ);

        $this->assertTrue(isset($filter->equal));
        $this->assertFalse(isset($filter->notEqual));
    }

    public function testGetPropertyKeepsTheDeclaredProperty(): void
    {
        $property = $this->property();
        $filter = new BinaryFilter($property, Operator::EQ);

        $this->assertSame($property, $filter->getProperty());
    }

    public function testInIsAList(): void
    {
        $filter = new BinaryFilter($this->property(), Operator::IN);
        $this->assertSame([], $filter->in);

        $filter->setCondition(Operator::IN, ['0101'], '0101');
        $this->assertSame(['0101'], $filter->in);
    }
}
