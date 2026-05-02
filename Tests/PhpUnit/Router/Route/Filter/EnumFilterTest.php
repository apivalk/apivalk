<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Router\Route\Filter\EnumFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

class EnumFilterTest extends TestCase
{
    private function prop(string $name = 'status'): EnumProperty
    {
        return new EnumProperty($name, '', ['active', 'inactive']);
    }

    public function testFactories(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, EnumFilter::equals($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_IN, EnumFilter::in($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('state');
        $filter = EnumFilter::equals($prop);

        $this->assertSame('state', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(EnumProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(EnumFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(EnumFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(EnumFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(EnumFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(EnumFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(EnumFilter::equals($this->prop())->isTypeLessThan());

        $this->assertTrue(EnumFilter::in($this->prop())->isTypeIn());
    }

    public function testValueCasting(): void
    {
        $filter = EnumFilter::equals($this->prop());

        $this->assertNull($filter->getValue());

        $filter->setValue('active');
        $this->assertSame('active', $filter->getValue());

        $filter->setValue(0);
        $this->assertSame('0', $filter->getValue());

        $filter->setValue(null);
        $this->assertNull($filter->getValue());
    }
}
