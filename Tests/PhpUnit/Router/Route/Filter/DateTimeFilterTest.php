<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Router\Route\Filter\DateTimeFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

class DateTimeFilterTest extends TestCase
{
    private function prop(string $name = 'updated_at'): DateTimeProperty
    {
        return new DateTimeProperty($name);
    }

    public function testFactories(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, DateTimeFilter::equals($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_IN, DateTimeFilter::in($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_GREATER_THAN, DateTimeFilter::greaterThan($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_LESS_THAN, DateTimeFilter::lessThan($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('deleted_at');
        $filter = DateTimeFilter::equals($prop);

        $this->assertSame('deleted_at', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(DateTimeProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
        $this->assertSame('date-time', $filter->getProperty()->getFormat());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(DateTimeFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(DateTimeFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(DateTimeFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(DateTimeFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(DateTimeFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(DateTimeFilter::equals($this->prop())->isTypeLessThan());

        $this->assertTrue(DateTimeFilter::in($this->prop())->isTypeIn());
        $this->assertTrue(DateTimeFilter::greaterThan($this->prop())->isTypeGreaterThan());
        $this->assertTrue(DateTimeFilter::lessThan($this->prop())->isTypeLessThan());
    }

    public function testValueFromString(): void
    {
        $filter = DateTimeFilter::equals($this->prop());
        $filter->setValue('2023-06-15 14:30:00');

        $this->assertInstanceOf(\DateTime::class, $filter->getValue());
        $this->assertSame('2023-06-15 14:30:00', $filter->getValue()->format('Y-m-d H:i:s'));
    }

    public function testValueFromDateTimeObject(): void
    {
        $filter = DateTimeFilter::equals($this->prop());
        $dt = new \DateTime('2023-06-15T14:30:00Z');
        $filter->setValue($dt);

        $this->assertSame($dt, $filter->getValue());
    }

    public function testValueNull(): void
    {
        $filter = DateTimeFilter::equals($this->prop());
        $filter->setValue('2023-06-15 14:30:00');
        $filter->setValue(null);

        $this->assertNull($filter->getValue());
    }

    public function testInvalidStringYieldsNull(): void
    {
        $filter = DateTimeFilter::equals($this->prop());
        $filter->setValue('not-a-datetime-at-all!!!invalid@@@');

        $this->assertNull($filter->getValue());
    }
}
