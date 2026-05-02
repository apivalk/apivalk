<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Router\Route\Filter\BinaryFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

class BinaryFilterTest extends TestCase
{
    private function prop(string $name = 'data'): BinaryProperty
    {
        return new BinaryProperty($name);
    }

    public function testFactories(): void
    {
        $this->assertSame(FilterInterface::TYPE_EQUALS, BinaryFilter::equals($this->prop())->getType());
        $this->assertSame(FilterInterface::TYPE_IN, BinaryFilter::in($this->prop())->getType());
    }

    public function testGetters(): void
    {
        $prop = $this->prop('checksum');
        $filter = BinaryFilter::equals($prop);

        $this->assertSame('checksum', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
        $this->assertInstanceOf(BinaryProperty::class, $filter->getProperty());
        $this->assertSame($prop, $filter->getProperty());
    }

    public function testTypeChecks(): void
    {
        $this->assertTrue(BinaryFilter::equals($this->prop())->isTypeEquals());
        $this->assertFalse(BinaryFilter::equals($this->prop())->isTypeIn());
        $this->assertFalse(BinaryFilter::equals($this->prop())->isTypeLike());
        $this->assertFalse(BinaryFilter::equals($this->prop())->isTypeContains());
        $this->assertFalse(BinaryFilter::equals($this->prop())->isTypeGreaterThan());
        $this->assertFalse(BinaryFilter::equals($this->prop())->isTypeLessThan());

        $this->assertTrue(BinaryFilter::in($this->prop())->isTypeIn());
    }

    public function testValueCasting(): void
    {
        $filter = BinaryFilter::equals($this->prop());

        $this->assertNull($filter->getValue());

        $filter->setValue('abc123');
        $this->assertSame('abc123', $filter->getValue());

        $filter->setValue(99);
        $this->assertSame('99', $filter->getValue());

        $filter->setValue(null);
        $this->assertNull($filter->getValue());
    }
}
