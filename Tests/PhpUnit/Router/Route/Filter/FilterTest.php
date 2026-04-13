<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Router\Route\Filter\DateFilter;
use apivalk\apivalk\Router\Route\Filter\DateTimeFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\FloatFilter;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testGetters(): void
    {
        $filter = new StringFilter(FilterInterface::TYPE_EQUALS, new StringProperty('status'));
        $this->assertSame('status', $filter->getField());
        $this->assertSame(FilterInterface::TYPE_EQUALS, $filter->getType());
    }

    public function testStaticFactories(): void
    {
        $prop = new StringProperty('f');
        $this->assertSame(FilterInterface::TYPE_EQUALS, StringFilter::equals($prop)->getType());
        $this->assertSame(FilterInterface::TYPE_IN, StringFilter::in($prop)->getType());
        $this->assertSame(FilterInterface::TYPE_LIKE, StringFilter::like($prop)->getType());
        $this->assertSame(FilterInterface::TYPE_CONTAINS, StringFilter::contains($prop)->getType());

        $intProp = new IntegerProperty('n');
        $this->assertSame(FilterInterface::TYPE_EQUALS, IntegerFilter::equals($intProp)->getType());
        $this->assertSame(FilterInterface::TYPE_IN, IntegerFilter::in($intProp)->getType());
        $this->assertSame(FilterInterface::TYPE_GREATER_THAN, IntegerFilter::greaterThan($intProp)->getType());
        $this->assertSame(FilterInterface::TYPE_LESS_THAN, IntegerFilter::lessThan($intProp)->getType());
    }

    public function testTypeChecks(): void
    {
        $prop = new StringProperty('f');
        $intProp = new IntegerProperty('n');
        $this->assertTrue(StringFilter::equals($prop)->isTypeEquals());
        $this->assertTrue(StringFilter::in($prop)->isTypeIn());
        $this->assertTrue(StringFilter::like($prop)->isTypeLike());
        $this->assertTrue(StringFilter::contains($prop)->isTypeContains());

        $this->assertTrue(IntegerFilter::greaterThan($intProp)->isTypeGreaterThan());
        $this->assertTrue(IntegerFilter::lessThan($intProp)->isTypeLessThan());

        $filter = StringFilter::equals($prop);
        $this->assertFalse($filter->isTypeIn());
    }

    public function testGetDefaultProperty(): void
    {
        $property = new StringProperty('status', 'Filter results by `status` (equals).');
        $property->setIsRequired(false);
        $filter = StringFilter::equals($property);

        $this->assertInstanceOf(StringProperty::class, $filter->getProperty());
        $this->assertSame('status', $filter->getProperty()->getPropertyName());
    }

    public function testValueCasting(): void
    {
        $filter = StringFilter::equals(new StringProperty('status'));
        $filter->setValue('123');
        $this->assertSame('123', $filter->getValue());

        $intProp = new IntegerProperty('count', '', IntegerProperty::FORMAT_INT32);
        $intFilter = IntegerFilter::equals($intProp);
        $intFilter->setValue('10');
        $this->assertSame(10, $intFilter->getValue());

        $floatProp = new FloatProperty('price', '', FloatProperty::FORMAT_FLOAT);
        $floatFilter = FloatFilter::equals($floatProp);
        $floatFilter->setValue('10.5');
        $this->assertSame(10.5, $floatFilter->getValue());

        $dateProp = new DateProperty('created_at');
        $dateFilter = DateFilter::equals($dateProp);
        $dateFilter->setValue('2023-01-01');
        $this->assertInstanceOf(\DateTime::class, $dateFilter->getValue());
        $this->assertSame('2023-01-01', $dateFilter->getValue()->format('Y-m-d'));
        $this->assertSame('date', $dateFilter->getProperty()->getFormat());

        $dateTimeProp = new DateTimeProperty('updated_at');
        $dateTimeFilter = DateTimeFilter::equals($dateTimeProp);
        $dateTimeFilter->setValue('2023-01-01 12:00:00');
        $this->assertInstanceOf(\DateTime::class, $dateTimeFilter->getValue());
        $this->assertSame('2023-01-01 12:00:00', $dateTimeFilter->getValue()->format('Y-m-d H:i:s'));
        $this->assertSame('date-time', $dateTimeFilter->getProperty()->getFormat());
    }
}
