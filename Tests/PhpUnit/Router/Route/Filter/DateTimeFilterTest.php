<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Router\Route\Filter\DateTimeFilter;
use apivalk\apivalk\Router\Route\Filter\Operator;
use PHPUnit\Framework\TestCase;

class DateTimeFilterTest extends TestCase
{
    private function property(string $name = 'field'): DateTimeProperty
    {
        return new DateTimeProperty($name, 'Description');
    }

    public function testSupportedOperators(): void
    {
        $this->assertSame([Operator::EQ, Operator::NEQ, Operator::IN, Operator::GT, Operator::GTE, Operator::LT, Operator::LTE, Operator::NULL], DateTimeFilter::supportedOperators());
    }

    public function testDeclaredOperatorsAreKeptInOrder(): void
    {
        $filter = new DateTimeFilter($this->property(), Operator::NEQ, Operator::EQ);

        $this->assertSame([Operator::NEQ, Operator::EQ], $filter->getAllowedOperators());
        $this->assertSame(Operator::NEQ, $filter->getDefaultOperator());
        $this->assertTrue($filter->allows(Operator::EQ));
    }

    public function testRejectsAnEmptyOperatorList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('declares no operator');

        new DateTimeFilter($this->property());
    }

    public function testRejectsAnUnsupportedOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported by');

        new DateTimeFilter($this->property(), Operator::LIKE);
    }

    public function testRejectsADuplicateOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate operator');

        new DateTimeFilter($this->property(), Operator::EQ, Operator::EQ);
    }

    public function testConditionRoundTrip(): void
    {
        $filter = new DateTimeFilter($this->property('created'), Operator::EQ);

        $this->assertFalse($filter->has(Operator::EQ));
        $this->assertNull($filter->raw(Operator::EQ));

        $filter->setCondition(Operator::EQ, new \DateTime('2026-01-15T10:00:00+00:00'), '2026-01-15T10:00:00+00:00');

        $this->assertTrue($filter->has(Operator::EQ));
        $this->assertEquals(new \DateTime('2026-01-15T10:00:00+00:00'), $filter->equal);
        $this->assertSame('2026-01-15T10:00:00+00:00', $filter->raw(Operator::EQ));
        $this->assertSame('created', $filter->getField());
    }

    public function testSetConditionRejectsAnUndeclaredOperator(): void
    {
        $filter = new DateTimeFilter($this->property(), Operator::EQ);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not allowed on field');

        $filter->setCondition(Operator::NEQ, new \DateTime('2026-01-15T10:00:00+00:00'), '2026-01-15T10:00:00+00:00');
    }

    public function testReadingAnUndeclaredOperatorThrows(): void
    {
        $filter = new DateTimeFilter($this->property(), Operator::EQ);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('is not declared on field');

        $filter->notEqual;
    }

    public function testReadingAnUnknownAccessorThrows(): void
    {
        $filter = new DateTimeFilter($this->property(), Operator::EQ);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown filter accessor');

        $filter->nonsense;
    }

    public function testIssetReflectsTheDeclaredOperators(): void
    {
        $filter = new DateTimeFilter($this->property(), Operator::EQ);

        $this->assertTrue(isset($filter->equal));
        $this->assertFalse(isset($filter->notEqual));
    }

    public function testGetPropertyKeepsTheDeclaredProperty(): void
    {
        $property = $this->property();
        $filter = new DateTimeFilter($property, Operator::EQ);

        $this->assertSame($property, $filter->getProperty());
    }

    public function testInIsAList(): void
    {
        $filter = new DateTimeFilter($this->property(), Operator::IN);
        $this->assertSame([], $filter->in);

        $filter->setCondition(Operator::IN, [new \DateTime('2026-01-15T10:00:00+00:00')], '2026-01-15T10:00:00+00:00');
        $this->assertCount(1, $filter->in);
        $this->assertInstanceOf(\DateTime::class, $filter->in[0]);
    }
}
