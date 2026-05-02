<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Router\Route\Filter\BooleanFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

class BooleanFilterTest extends TestCase
{
    private function prop(string $name = 'active'): BooleanProperty
    {
        return new BooleanProperty($name, '', false);
    }

    public function testFactory(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, BooleanFilter::equals($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('enabled');
        $filter = BooleanFilter::equals($prop);

        $this->assertSame('enabled', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(BooleanProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(BooleanFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(BooleanFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(BooleanFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(BooleanFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(BooleanFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(BooleanFilter::equals($this->prop())->isTypeLessThan());
    }

    public function testValueCasting(): void
    {
        $filter = BooleanFilter::equals($this->prop());

        $this->assertNull($filter->getValue());

        $filter->setValue(true);
        $this->assertTrue($filter->getValue());

        $filter->setValue(false);
        $this->assertFalse($filter->getValue());

        $filter->setValue(1);
        $this->assertTrue($filter->getValue());

        $filter->setValue(0);
        $this->assertFalse($filter->getValue());

        $filter->setValue('1');
        $this->assertTrue($filter->getValue());

        $filter->setValue('');
        $this->assertFalse($filter->getValue());

        $filter->setValue(null);
        $this->assertNull($filter->getValue());
    }
}
