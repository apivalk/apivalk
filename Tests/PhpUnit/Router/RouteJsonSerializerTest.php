<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Documentation\Property\ByteProperty;
use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Filter\BinaryFilter;
use apivalk\apivalk\Router\Route\Filter\ByteFilter;
use apivalk\apivalk\Router\Route\Filter\DateFilter;
use apivalk\apivalk\Router\Route\Filter\DateTimeFilter;
use apivalk\apivalk\Router\Route\Filter\EnumFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\FloatFilter;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\RouteJsonSerializer;
use PHPUnit\Framework\TestCase;

class RouteJsonSerializerTest extends TestCase
{
    public function testSerializeDeserialize(): void
    {
        $route = new Route(
            '/users',
            new GetMethod(),
            'User list',
            null,
            [],
            null,
            new IpRateLimit('ip_limit', 10, 60)
        );

        $serialized = RouteJsonSerializer::serialize($route);
        $this->assertEquals('/users', $serialized['url']);
        $this->assertEquals('GET', $serialized['method']);
        $this->assertEquals('User list', $serialized['description']);
        $this->assertEquals('apivalk\apivalk\Router\RateLimit\IpRateLimit', $serialized['rateLimit']['class']);
        $this->assertEquals('ip_limit', $serialized['rateLimit']['name']);

        $json = json_encode($serialized);
        $deserialized = RouteJsonSerializer::deserialize($json);

        $this->assertEquals($route->getUrl(), $deserialized->getUrl());
        $this->assertEquals($route->getMethod()->getName(), $deserialized->getMethod()->getName());
        $this->assertEquals($route->getDescription(), $deserialized->getDescription());
        $this->assertNull($deserialized->getSummary());
        $this->assertInstanceOf(IpRateLimit::class, $deserialized->getRateLimit());
        $this->assertEquals('ip_limit', $deserialized->getRateLimit()->getName());
    }

    public function testSerializeDeserializeWithSummary(): void
    {
        $route = new Route(
            '/users',
            new GetMethod(),
            'User list',
            'Test',
            [],
            null,
            new IpRateLimit('ip_limit', 10, 60)
        );

        $serialized = RouteJsonSerializer::serialize($route);
        $this->assertEquals('Test', $serialized['summary']);

        $json = json_encode($serialized);
        $deserialized = RouteJsonSerializer::deserialize($json);

        $this->assertEquals($route->getSummary(), $deserialized->getSummary());
    }

    public function testSerializeDeserializeWithoutRateLimit(): void
    {
        $route = new Route(
            '/users',
            new GetMethod()
        );

        $serialized = RouteJsonSerializer::serialize($route);
        $this->assertNull($serialized['rateLimit']);

        $json = json_encode($serialized);
        $deserialized = RouteJsonSerializer::deserialize($json);

        $this->assertNull($deserialized->getRateLimit());
    }

    public function testStringFilterRoundTrip(): void
    {
        $property = new StringProperty('name', 'User name');
        $property->setMinLength(2)->setMaxLength(100)->setPattern('^[a-zA-Z]+$')->setDefault('John');

        $route = $this->createRouteWithFilters([
            StringFilter::equals($property),
        ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(StringFilter::class, $filter);
        $this->assertEquals(FilterInterface::TYPE_EQUALS, $filter->getType());

        /** @var StringProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(StringProperty::class, $restoredProperty);
        $this->assertEquals('name', $restoredProperty->getPropertyName());
        $this->assertEquals('User name', $restoredProperty->getPropertyDescription());
        $this->assertEquals(2, $restoredProperty->getMinLength());
        $this->assertEquals(100, $restoredProperty->getMaxLength());
        $this->assertEquals('^[a-zA-Z]+$', $restoredProperty->getPattern());
        $this->assertEquals('John', $restoredProperty->getDefault());
    }

    public function testEnumFilterRoundTrip(): void
    {
        $property = new EnumProperty('status', 'Contract status', ['active', 'inactive', 'pending']);
        $property->setDefault('active');

        $route = $this->createRouteWithFilters([
            EnumFilter::equals($property),
        ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(EnumFilter::class, $filter);
        $this->assertEquals(FilterInterface::TYPE_EQUALS, $filter->getType());

        /** @var EnumProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(EnumProperty::class, $restoredProperty);
        $this->assertEquals('status', $restoredProperty->getPropertyName());
        $this->assertEquals(['active', 'inactive', 'pending'], $restoredProperty->getEnums());
        $this->assertEquals('active', $restoredProperty->getDefault());
    }

    public function testDateFilterRoundTrip(): void
    {
        $property = new DateProperty('birthdate', 'Date of birth');
        $property->setDefault('2000-01-01');

        $route = $this->createRouteWithFilters([
            DateFilter::equals($property),
        ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(DateFilter::class, $filter);

        /** @var DateProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(DateProperty::class, $restoredProperty);
        $this->assertEquals('birthdate', $restoredProperty->getPropertyName());
        $this->assertEquals('date', $restoredProperty->getFormat());
        $this->assertEquals('2000-01-01', $restoredProperty->getDefault());
    }

    public function testDateTimeFilterRoundTrip(): void
    {
        $property = new DateTimeProperty('createdAt', 'Creation timestamp');
        $property->setDefault('2024-01-01T00:00:00Z');

        $route = $this->createRouteWithFilters([
            DateTimeFilter::equals($property),
        ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(DateTimeFilter::class, $filter);

        /** @var DateTimeProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(DateTimeProperty::class, $restoredProperty);
        $this->assertEquals('createdAt', $restoredProperty->getPropertyName());
        $this->assertEquals('date-time', $restoredProperty->getFormat());
        $this->assertEquals('2024-01-01T00:00:00Z', $restoredProperty->getDefault());
    }

    public function testByteFilterRoundTrip(): void
    {
        $property = new ByteProperty('payload', 'Base64 payload');
        $property->setMinLength(4)->setMaxLength(1024)->setPattern('^[A-Za-z0-9+/=]+$')->setDefault('dGVzdA==');

        $route = $this->createRouteWithFilters([
            ByteFilter::equals($property),
        ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(ByteFilter::class, $filter);

        /** @var ByteProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(ByteProperty::class, $restoredProperty);
        $this->assertEquals('payload', $restoredProperty->getPropertyName());
        $this->assertEquals(4, $restoredProperty->getMinLength());
        $this->assertEquals(1024, $restoredProperty->getMaxLength());
        $this->assertEquals('^[A-Za-z0-9+/=]+$', $restoredProperty->getPattern());
        $this->assertEquals('dGVzdA==', $restoredProperty->getDefault());
        $this->assertEquals('byte', $restoredProperty->getFormat());
    }

    public function testBinaryFilterRoundTrip(): void
    {
        $property = new BinaryProperty('file', 'Binary data');
        $property->setMinLength(1)->setMaxLength(2048)->setPattern('^.+$')->setDefault('data');

        $route = $this->createRouteWithFilters([
            BinaryFilter::equals($property),
        ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(BinaryFilter::class, $filter);

        /** @var BinaryProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(BinaryProperty::class, $restoredProperty);
        $this->assertEquals('file', $restoredProperty->getPropertyName());
        $this->assertEquals(1, $restoredProperty->getMinLength());
        $this->assertEquals(2048, $restoredProperty->getMaxLength());
        $this->assertEquals('^.+$', $restoredProperty->getPattern());
        $this->assertEquals('data', $restoredProperty->getDefault());
        $this->assertEquals('binary', $restoredProperty->getFormat());
    }

    public function testIntegerFilterRoundTrip(): void
    {
        $property = new IntegerProperty('age', 'User age', IntegerProperty::FORMAT_INT32);
        $property->setMinimumValue(0)
            ->setMaximumValue(150)
            ->setIsExclusiveMinimum(true)
            ->setIsExclusiveMaximum(false);

        $route = $this->createRouteWithFilters([
            IntegerFilter::greaterThan($property),
        ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(IntegerFilter::class, $filter);
        $this->assertEquals(FilterInterface::TYPE_GREATER_THAN, $filter->getType());

        /** @var IntegerProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(IntegerProperty::class, $restoredProperty);
        $this->assertEquals('age', $restoredProperty->getPropertyName());
        $this->assertEquals('int32', $restoredProperty->getFormat());
        $this->assertEquals(0, $restoredProperty->getMinimumValue());
        $this->assertEquals(150, $restoredProperty->getMaximumValue());
        $this->assertTrue($restoredProperty->isExclusiveMinimum());
        $this->assertFalse($restoredProperty->isExclusiveMaximum());
    }

    public function testFloatFilterRoundTrip(): void
    {
        $property = new FloatProperty('price', 'Product price', FloatProperty::FORMAT_FLOAT);
        $property->setMinimumValue(0.01)
            ->setMaximumValue(9999.99)
            ->setIsExclusiveMinimum(false)
            ->setIsExclusiveMaximum(true);

        $route = $this->createRouteWithFilters([
            FloatFilter::lessThan($property),
        ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(FloatFilter::class, $filter);
        $this->assertEquals(FilterInterface::TYPE_LESS_THAN, $filter->getType());

        /** @var FloatProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(FloatProperty::class, $restoredProperty);
        $this->assertEquals('price', $restoredProperty->getPropertyName());
        $this->assertEquals('float', $restoredProperty->getFormat());
        $this->assertEquals(0.01, $restoredProperty->getMinimumValue(), '', 0.001);
        $this->assertEquals(9999.99, $restoredProperty->getMaximumValue(), '', 0.001);
        $this->assertFalse($restoredProperty->isExclusiveMinimum());
        $this->assertTrue($restoredProperty->isExclusiveMaximum());
    }

    public function testAllFilterTypesTogetherRoundTrip(): void
    {
        $stringProp = new StringProperty('name', 'Name');
        $stringProp->setMinLength(1)->setMaxLength(255);

        $enumProp = new EnumProperty('status', 'Status', ['active', 'inactive']);

        $dateProp = new DateProperty('date', 'Date');

        $dateTimeProp = new DateTimeProperty('timestamp', 'Timestamp');

        $byteProp = new ByteProperty('payload', 'Payload');
        $byteProp->setMinLength(4);

        $binaryProp = new BinaryProperty('file', 'File');
        $binaryProp->setMaxLength(5000);

        $intProp = new IntegerProperty('count', 'Count', IntegerProperty::FORMAT_INT32);
        $intProp->setMinimumValue(0)->setIsExclusiveMinimum(false);

        $floatProp = new FloatProperty('amount', 'Amount', FloatProperty::FORMAT_DOUBLE);
        $floatProp->setMaximumValue(100000.0)->setIsExclusiveMaximum(false);

        $filters = [
            StringFilter::like($stringProp),
            EnumFilter::in($enumProp),
            DateFilter::greaterThan($dateProp),
            DateTimeFilter::lessThan($dateTimeProp),
            ByteFilter::equals($byteProp),
            BinaryFilter::equals($binaryProp),
            IntegerFilter::equals($intProp),
            FloatFilter::greaterThan($floatProp),
        ];

        $route = $this->createRouteWithFilters($filters);
        $deserialized = $this->serializeAndDeserialize($route);

        $restoredFilters = $deserialized->getFilters();
        $this->assertCount(8, $restoredFilters);

        $this->assertInstanceOf(StringFilter::class, $restoredFilters[0]);
        $this->assertInstanceOf(StringProperty::class, $restoredFilters[0]->getProperty());
        $this->assertEquals(FilterInterface::TYPE_LIKE, $restoredFilters[0]->getType());
        $this->assertEquals(1, $restoredFilters[0]->getProperty()->getMinLength());
        $this->assertEquals(255, $restoredFilters[0]->getProperty()->getMaxLength());

        $this->assertInstanceOf(EnumFilter::class, $restoredFilters[1]);
        $this->assertInstanceOf(EnumProperty::class, $restoredFilters[1]->getProperty());
        $this->assertEquals(FilterInterface::TYPE_IN, $restoredFilters[1]->getType());
        $this->assertEquals(['active', 'inactive'], $restoredFilters[1]->getProperty()->getEnums());

        $this->assertInstanceOf(DateFilter::class, $restoredFilters[2]);
        $this->assertInstanceOf(DateProperty::class, $restoredFilters[2]->getProperty());
        $this->assertEquals(FilterInterface::TYPE_GREATER_THAN, $restoredFilters[2]->getType());

        $this->assertInstanceOf(DateTimeFilter::class, $restoredFilters[3]);
        $this->assertInstanceOf(DateTimeProperty::class, $restoredFilters[3]->getProperty());
        $this->assertEquals(FilterInterface::TYPE_LESS_THAN, $restoredFilters[3]->getType());

        $this->assertInstanceOf(ByteFilter::class, $restoredFilters[4]);
        $this->assertInstanceOf(ByteProperty::class, $restoredFilters[4]->getProperty());
        $this->assertEquals(4, $restoredFilters[4]->getProperty()->getMinLength());

        $this->assertInstanceOf(BinaryFilter::class, $restoredFilters[5]);
        $this->assertInstanceOf(BinaryProperty::class, $restoredFilters[5]->getProperty());
        $this->assertEquals(5000, $restoredFilters[5]->getProperty()->getMaxLength());

        $this->assertInstanceOf(IntegerFilter::class, $restoredFilters[6]);
        $this->assertInstanceOf(IntegerProperty::class, $restoredFilters[6]->getProperty());
        $this->assertEquals('int32', $restoredFilters[6]->getProperty()->getFormat());
        $this->assertEquals(0, $restoredFilters[6]->getProperty()->getMinimumValue());

        $this->assertInstanceOf(FloatFilter::class, $restoredFilters[7]);
        $this->assertInstanceOf(FloatProperty::class, $restoredFilters[7]->getProperty());
        $this->assertEquals('double', $restoredFilters[7]->getProperty()->getFormat());
        $this->assertEquals(100000.0, $restoredFilters[7]->getProperty()->getMaximumValue());
    }

    /**
     * @param FilterInterface[] $filters
     */
    private function createRouteWithFilters(array $filters): Route
    {
        return new Route(
            '/test',
            new GetMethod(),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $filters
        );
    }

    private function serializeAndDeserialize(Route $route): Route
    {
        $serialized = RouteJsonSerializer::serialize($route);
        $json = json_encode($serialized);

        return RouteJsonSerializer::deserialize($json);
    }
}
