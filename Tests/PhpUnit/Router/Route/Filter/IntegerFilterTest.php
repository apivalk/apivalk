<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
use PHPUnit\Framework\TestCase;

class IntegerFilterTest extends TestCase
{
    private function prop(string $name = 'count'): IntegerProperty
    {
        return new IntegerProperty($name);
    }

    public function testFactories(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, IntegerFilter::equals($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_IN, IntegerFilter::in($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_GREATER_THAN, IntegerFilter::greaterThan($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_LESS_THAN, IntegerFilter::lessThan($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('age');
        $filter = IntegerFilter::equals($prop);

        $this->assertSame('age', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(IntegerProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(IntegerFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(IntegerFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(IntegerFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(IntegerFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(IntegerFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(IntegerFilter::equals($this->prop())->isTypeLessThan());

        $this->assertTrue(IntegerFilter::in($this->prop())->isTypeIn());
        $this->assertTrue(IntegerFilter::greaterThan($this->prop())->isTypeGreaterThan());
        $this->assertTrue(IntegerFilter::lessThan($this->prop())->isTypeLessThan());
    }

    public function testValueCasting(): void
    {
        $filter = IntegerFilter::equals($this->prop());

        $this->assertNull($filter->getValue());

        $filter->setValue(10);
        $this->assertSame(10, $filter->getValue());

        $filter->setValue('42');
        $this->assertSame(42, $filter->getValue());

        $filter->setValue(3.9);
        $this->assertSame(3, $filter->getValue());

        $filter->setValue(null);
        $this->assertNull($filter->getValue());
    }
}
