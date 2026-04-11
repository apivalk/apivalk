<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Router\Route\Filter\DateFilter;
use apivalk\apivalk\Router\Route\Filter\DateTimeFilter;
use apivalk\apivalk\Router\Route\Filter\AbstractFilter;
use apivalk\apivalk\Router\Route\Filter\NumberFilter;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testGetters(): void
    {
        $filter = new StringFilter(AbstractFilter::TYPE_EQUALS, new StringProperty('status'));
        $this->assertSame('status', $filter->getField());
        $this->assertSame(AbstractFilter::TYPE_EQUALS, $filter->getType());
    }

    public function testStaticFactories(): void
    {
        $prop = new StringProperty('f');
        $this->assertSame(AbstractFilter::TYPE_EQUALS, StringFilter::equals($prop)->getType());
        $this->assertSame(AbstractFilter::TYPE_IN, StringFilter::in($prop)->getType());
        $this->assertSame(AbstractFilter::TYPE_LIKE, StringFilter::like($prop)->getType());
        $this->assertSame(AbstractFilter::TYPE_CONTAINS, StringFilter::contains($prop)->getType());

        $numProp = new NumberProperty('n');
        $this->assertSame(AbstractFilter::TYPE_EQUALS, NumberFilter::equals($numProp)->getType());
        $this->assertSame(AbstractFilter::TYPE_IN, NumberFilter::in($numProp)->getType());
        $this->assertSame(AbstractFilter::TYPE_GREATER_THAN, NumberFilter::greaterThan($numProp)->getType());
        $this->assertSame(AbstractFilter::TYPE_LESS_THAN, NumberFilter::lessThan($numProp)->getType());
    }

    public function testTypeChecks(): void
    {
        $prop = new StringProperty('f');
        $numProp = new NumberProperty('n');
        $this->assertTrue(StringFilter::equals($prop)->isTypeEquals());
        $this->assertTrue(StringFilter::in($prop)->isTypeIn());
        $this->assertTrue(StringFilter::like($prop)->isTypeLike());
        $this->assertTrue(StringFilter::contains($prop)->isTypeContains());

        $this->assertTrue(NumberFilter::greaterThan($numProp)->isTypeGreaterThan());
        $this->assertTrue(NumberFilter::lessThan($numProp)->isTypeLessThan());

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
        $filter->setValue(123);
        $this->assertSame('123', $filter->getValue());

        $numProp = new NumberProperty('count');
        $numProp->setFormat(NumberProperty::FORMAT_INT32);
        $intFilter = NumberFilter::equals($numProp);
        $intFilter->setValue('10');
        $this->assertSame(10, $intFilter->getValue());

        $floatProp = new NumberProperty('price');
        $floatProp->setFormat(NumberProperty::FORMAT_FLOAT);
        $floatFilter = NumberFilter::equals($floatProp);
        $floatFilter->setValue('10.5');
        $this->assertSame(10.5, $floatFilter->getValue());

        $dateProp = new StringProperty('created_at');
        $dateProp->setFormat(StringProperty::FORMAT_DATE);
        $dateFilter = DateFilter::equals($dateProp);
        $dateFilter->setValue('2023-01-01');
        $this->assertInstanceOf(\DateTime::class, $dateFilter->getValue());
        $this->assertSame('2023-01-01', $dateFilter->getValue()->format('Y-m-d'));
        $this->assertSame(StringProperty::FORMAT_DATE, $dateFilter->getProperty()->getFormat());

        $dateTimeProp = new StringProperty('updated_at');
        $dateTimeProp->setFormat(StringProperty::FORMAT_DATE_TIME);
        $dateTimeFilter = DateTimeFilter::equals($dateTimeProp);
        $dateTimeFilter->setValue('2023-01-01 12:00:00');
        $this->assertInstanceOf(\DateTime::class, $dateTimeFilter->getValue());
        $this->assertSame('2023-01-01 12:00:00', $dateTimeFilter->getValue()->format('Y-m-d H:i:s'));
        $this->assertSame(StringProperty::FORMAT_DATE_TIME, $dateTimeFilter->getProperty()->getFormat());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDateFilterInvalidFormatThrowsException(): void
    {
        $prop = new StringProperty('d');
        // format is default (null or string), not FORMAT_DATE
        DateFilter::equals($prop);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDateTimeFilterInvalidFormatThrowsException(): void
    {
        $prop = new StringProperty('dt');
        // format is default, not FORMAT_DATE_TIME
        DateTimeFilter::equals($prop);
    }
}
