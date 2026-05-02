<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Router\Route\Filter\DateFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

class DateFilterTest extends TestCase
{
    private function prop(string $name = 'created_at'): DateProperty
    {
        return new DateProperty($name);
    }

    public function testFactories(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, DateFilter::equals($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_IN, DateFilter::in($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_GREATER_THAN, DateFilter::greaterThan($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_LESS_THAN, DateFilter::lessThan($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('published_at');
        $filter = DateFilter::equals($prop);

        $this->assertSame('published_at', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(DateProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
        $this->assertSame('date', $filter->getProperty()->getFormat());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(DateFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(DateFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(DateFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(DateFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(DateFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(DateFilter::equals($this->prop())->isTypeLessThan());

        $this->assertTrue(DateFilter::in($this->prop())->isTypeIn());
        $this->assertTrue(DateFilter::greaterThan($this->prop())->isTypeGreaterThan());
        $this->assertTrue(DateFilter::lessThan($this->prop())->isTypeLessThan());
    }

    public function testValueFromString(): void
    {
        $filter = DateFilter::equals($this->prop());
        $filter->setValue('2023-06-15');

        $this->assertInstanceOf(\DateTime::class, $filter->getValue());
        $this->assertSame('2023-06-15', $filter->getValue()->format('Y-m-d'));
    }

    public function testValueFromDateTimeObject(): void
    {
        $filter = DateFilter::equals($this->prop());
        $dt = new \DateTime('2023-06-15');
        $filter->setValue($dt);

        $this->assertSame($dt, $filter->getValue());
    }

    public function testValueNull(): void
    {
        $filter = DateFilter::equals($this->prop());
        $filter->setValue('2023-06-15');
        $filter->setValue(null);

        $this->assertNull($filter->getValue());
    }

    public function testInvalidStringYieldsNull(): void
    {
        $filter = DateFilter::equals($this->prop());
        $filter->setValue('not-a-date-at-all!!!invalid@@@');

        $this->assertNull($filter->getValue());
    }
}
