<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\ByteProperty;
use apivalk\apivalk\Router\Route\Filter\ByteFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

class ByteFilterTest extends TestCase
{
    private function prop(string $name = 'payload'): ByteProperty
    {
        return new ByteProperty($name);
    }

    public function testFactories(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, ByteFilter::equals($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_IN, ByteFilter::in($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('content');
        $filter = ByteFilter::equals($prop);

        $this->assertSame('content', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(ByteProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(ByteFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(ByteFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(ByteFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(ByteFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(ByteFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(ByteFilter::equals($this->prop())->isTypeLessThan());

        $this->assertTrue(ByteFilter::in($this->prop())->isTypeIn());
    }

    public function testValueCasting(): void
    {
        $filter = ByteFilter::equals($this->prop());

        $this->assertNull($filter->getValue());

        $filter->setValue('dGVzdA==');
        $this->assertSame('dGVzdA==', $filter->getValue());

        $filter->setValue(0);
        $this->assertSame('0', $filter->getValue());

        $filter->setValue(null);
        $this->assertNull($filter->getValue());
    }
}
