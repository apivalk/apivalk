<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Http\Request\Parameter\Parameter;
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
use apivalk\apivalk\Router\Route\Filter\Operator;
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
                                                   new StringFilter($property, Operator::EQ),
                                               ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(StringFilter::class, $filter);
        $this->assertSame([Operator::EQ], $filter->getAllowedOperators());

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
                                                   new EnumFilter($property, Operator::EQ),
                                               ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(EnumFilter::class, $filter);
        $this->assertSame([Operator::EQ], $filter->getAllowedOperators());

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
                                                   new DateFilter($property, Operator::EQ),
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
                                                   new DateTimeFilter($property, Operator::EQ),
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
                                                   new ByteFilter($property, Operator::EQ),
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
                                                   new BinaryFilter($property, Operator::EQ),
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
                                                   new IntegerFilter($property, Operator::GT),
                                               ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(IntegerFilter::class, $filter);
        $this->assertSame([Operator::GT], $filter->getAllowedOperators());

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
                                                   new FloatFilter($property, Operator::LT),
                                               ]);

        $deserialized = $this->serializeAndDeserialize($route);

        $this->assertCount(1, $deserialized->getFilters());
        $filter = $deserialized->getFilters()[0];
        $this->assertInstanceOf(FloatFilter::class, $filter);
        $this->assertSame([Operator::LT], $filter->getAllowedOperators());

        /** @var FloatProperty $restoredProperty */
        $restoredProperty = $filter->getProperty();
        $this->assertInstanceOf(FloatProperty::class, $restoredProperty);
        $this->assertEquals('price', $restoredProperty->getPropertyName());
        $this->assertEquals('float', $restoredProperty->getFormat());
        $this->assertEqualsWithDelta(0.01, $restoredProperty->getMinimumValue(), 0.001);
        $this->assertEqualsWithDelta(9999.99, $restoredProperty->getMaximumValue(), 0.001);
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
            new StringFilter($stringProp, Operator::LIKE),
            new EnumFilter($enumProp, Operator::IN),
            new DateFilter($dateProp, Operator::GT),
            new DateTimeFilter($dateTimeProp, Operator::LT),
            new ByteFilter($byteProp, Operator::EQ),
            new BinaryFilter($binaryProp, Operator::EQ),
            new IntegerFilter($intProp, Operator::EQ),
            new FloatFilter($floatProp, Operator::GT),
        ];

        $route = $this->createRouteWithFilters($filters);
        $deserialized = $this->serializeAndDeserialize($route);

        $restoredFilters = $deserialized->getFilters();
        $this->assertCount(8, $restoredFilters);

        $this->assertInstanceOf(StringFilter::class, $restoredFilters[0]);
        $this->assertInstanceOf(StringProperty::class, $restoredFilters[0]->getProperty());
        $this->assertSame([Operator::LIKE], $restoredFilters[0]->getAllowedOperators());
        $this->assertEquals(1, $restoredFilters[0]->getProperty()->getMinLength());
        $this->assertEquals(255, $restoredFilters[0]->getProperty()->getMaxLength());

        $this->assertInstanceOf(EnumFilter::class, $restoredFilters[1]);
        $this->assertInstanceOf(EnumProperty::class, $restoredFilters[1]->getProperty());
        $this->assertSame([Operator::IN], $restoredFilters[1]->getAllowedOperators());
        $this->assertEquals(['active', 'inactive'], $restoredFilters[1]->getProperty()->getEnums());

        $this->assertInstanceOf(DateFilter::class, $restoredFilters[2]);
        $this->assertInstanceOf(DateProperty::class, $restoredFilters[2]->getProperty());
        $this->assertSame([Operator::GT], $restoredFilters[2]->getAllowedOperators());

        $this->assertInstanceOf(DateTimeFilter::class, $restoredFilters[3]);
        $this->assertInstanceOf(DateTimeProperty::class, $restoredFilters[3]->getProperty());
        $this->assertSame([Operator::LT], $restoredFilters[3]->getAllowedOperators());

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

    public function testFilterPropertyHasExactlyOneValidatorAfterRoundTrip(): void
    {
        // Verifies that double-init (once in constructor, once in PropertySerializer::deserialize)
        // remains idempotent and does not produce duplicate validators.
        $filters = [
            new StringFilter(new StringProperty('name', ''), Operator::EQ),
            new EnumFilter(new EnumProperty('status', '', ['a', 'b']), Operator::EQ),
            new IntegerFilter(new IntegerProperty('age', ''), Operator::GT),
            new FloatFilter(new FloatProperty('score', ''), Operator::LT),
            new DateFilter(new DateProperty('dob', ''), Operator::EQ),
            new DateTimeFilter(new DateTimeProperty('created_at', ''), Operator::GT),
            new ByteFilter(new ByteProperty('payload', ''), Operator::EQ),
            new BinaryFilter(new BinaryProperty('file', ''), Operator::EQ),
        ];

        $deserialized = $this->serializeAndDeserialize($this->createRouteWithFilters($filters));

        foreach ($deserialized->getFilters() as $filter) {
            $this->assertCount(1, $filter->getProperty()->getValidators());
        }
    }

    public function testEnumFilterStillValidatesAfterRoundTrip(): void
    {
        $property = new EnumProperty('status', '', ['active', 'inactive']);
        $route = $this->createRouteWithFilters([new EnumFilter($property, Operator::EQ)]);
        $deserialized = $this->serializeAndDeserialize($route);

        /** @var EnumFilter $filter */
        $filter = $deserialized->getFilters()[0];
        $this->assertCount(1, $filter->getProperty()->getValidators());

        // Validator from the deserialized property must reject values outside the enum
        $parameter = new Parameter('status', 'archived', 'archived');
        foreach ($filter->getProperty()->getValidators() as $validator) {
            $result = $validator->validate($parameter);
            $this->assertFalse($result->isSuccess());
        }
    }

    public function testExcludedFromDocumentationRoundTrip(): void
    {
        $route = Route::get('/internal')->excludeFromDocumentation();

        $serialized = RouteJsonSerializer::serialize($route);
        $this->assertTrue($serialized['excludedFromDocumentation']);

        $this->assertTrue($this->serializeAndDeserialize($route)->isExcludedFromDocumentation());
        $this->assertFalse($this->serializeAndDeserialize(Route::get('/users'))->isExcludedFromDocumentation());
    }

    public function testDeserializeCacheEntryWithoutExclusionFlag(): void
    {
        $serialized = RouteJsonSerializer::serialize(Route::get('/users'));
        unset($serialized['excludedFromDocumentation']);

        $deserialized = RouteJsonSerializer::deserialize(json_encode($serialized));

        $this->assertFalse($deserialized->isExcludedFromDocumentation());
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
