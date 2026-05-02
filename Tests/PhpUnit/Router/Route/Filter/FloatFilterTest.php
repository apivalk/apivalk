<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\FloatFilter;
use PHPUnit\Framework\TestCase;

class FloatFilterTest extends TestCase
{
    private function prop(string $name = 'price'): FloatProperty
    {
        return new FloatProperty($name);
    }

    public function testFactories(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, FloatFilter::equals($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_IN, FloatFilter::in($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_GREATER_THAN, FloatFilter::greaterThan($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_LESS_THAN, FloatFilter::lessThan($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('amount');
        $filter = FloatFilter::equals($prop);

        $this->assertSame('amount', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(FloatProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(FloatFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(FloatFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(FloatFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(FloatFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(FloatFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(FloatFilter::equals($this->prop())->isTypeLessThan());

        $this->assertTrue(FloatFilter::in($this->prop())->isTypeIn());
        $this->assertTrue(FloatFilter::greaterThan($this->prop())->isTypeGreaterThan());
        $this->assertTrue(FloatFilter::lessThan($this->prop())->isTypeLessThan());
    }

    public function testValueCasting(): void
    {
        $filter = FloatFilter::equals($this->prop());

        $this->assertNull($filter->getValue());

        $filter->setValue(10.5);
        $this->assertSame(10.5, $filter->getValue());

        $filter->setValue('3.14');
        $this->assertSame(3.14, $filter->getValue());

        $filter->setValue(42);
        $this->assertSame(42.0, $filter->getValue());

        $filter->setValue(null);
        $this->assertNull($filter->getValue());
    }
}
