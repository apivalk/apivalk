<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use PHPUnit\Framework\TestCase;

class StringFilterTest extends TestCase
{
    private function prop(string $name = 'field'): StringProperty
    {
        return new StringProperty($name);
    }

    public function testFactories(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, StringFilter::equals($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_IN, StringFilter::in($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_LIKE, StringFilter::like($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_CONTAINS, StringFilter::contains($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('status');
        $filter = StringFilter::equals($prop);

        $this->assertSame('status', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(StringProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(StringFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(StringFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(StringFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(StringFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(StringFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(StringFilter::equals($this->prop())->isTypeLessThan());

        $this->assertTrue(StringFilter::in($this->prop())->isTypeIn());
        $this->assertTrue(StringFilter::like($this->prop())->isTypeLike());
        $this->assertTrue(StringFilter::contains($this->prop())->isTypeContains());
    }

    public function testValueCasting(): void
    {
        $filter = StringFilter::equals($this->prop());

        $this->assertNull($filter->getValue());

        $filter->setValue('hello');
        $this->assertSame('hello', $filter->getValue());

        $filter->setValue(42);
        $this->assertSame('42', $filter->getValue());

        $filter->setValue(null);
        $this->assertNull($filter->getValue());
    }
}
