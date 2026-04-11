<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Filter\AbstractFilter;
use apivalk\apivalk\Router\Route\Filter\NumberFilter;
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
        $this->assertEquals('/users', $serialized['url']);
        $this->assertEquals('GET', $serialized['method']);
        $this->assertEquals('User list', $serialized['description']);
        $this->assertEquals('Test', $serialized['summary']);
        $this->assertEquals('apivalk\apivalk\Router\RateLimit\IpRateLimit', $serialized['rateLimit']['class']);
        $this->assertEquals('ip_limit', $serialized['rateLimit']['name']);

        $json = json_encode($serialized);
        $deserialized = RouteJsonSerializer::deserialize($json);

        $this->assertEquals($route->getUrl(), $deserialized->getUrl());
        $this->assertEquals($route->getMethod()->getName(), $deserialized->getMethod()->getName());
        $this->assertEquals($route->getDescription(), $deserialized->getDescription());
        $this->assertEquals($route->getSummary(), $deserialized->getSummary());
        $this->assertInstanceOf(IpRateLimit::class, $deserialized->getRateLimit());
        $this->assertEquals('ip_limit', $deserialized->getRateLimit()->getName());
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

    public function testSerializeDeserializeWithFilters(): void
    {
        $filters = [
            StringFilter::equals(new StringProperty('name')),
            NumberFilter::greaterThan(new NumberProperty('age')),
        ];

        $route = new Route(
            '/users',
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

        $serialized = RouteJsonSerializer::serialize($route);
        $this->assertCount(2, $serialized['filters']);
        $this->assertEquals(StringFilter::class, $serialized['filters'][0]['class']);
        $this->assertEquals(AbstractFilter::TYPE_EQUALS, $serialized['filters'][0]['type']);
        $this->assertEquals('name', $serialized['filters'][0]['property']['name']);
        $this->assertEquals(StringProperty::class, $serialized['filters'][0]['property']['class']);

        $this->assertEquals(NumberFilter::class, $serialized['filters'][1]['class']);
        $this->assertEquals(AbstractFilter::TYPE_GREATER_THAN, $serialized['filters'][1]['type']);
        $this->assertEquals('age', $serialized['filters'][1]['property']['name']);
        $this->assertEquals(NumberProperty::class, $serialized['filters'][1]['property']['class']);

        $json = json_encode($serialized);
        $deserialized = RouteJsonSerializer::deserialize($json);

        $this->assertCount(2, $deserialized->getFilters());
        $this->assertInstanceOf(StringFilter::class, $deserialized->getFilters()[0]);
        $this->assertEquals('name', $deserialized->getFilters()[0]->getField());
        $this->assertEquals(AbstractFilter::TYPE_EQUALS, $deserialized->getFilters()[0]->getType());

        $this->assertInstanceOf(NumberFilter::class, $deserialized->getFilters()[1]);
        $this->assertEquals('age', $deserialized->getFilters()[1]->getField());
        $this->assertEquals(AbstractFilter::TYPE_GREATER_THAN, $deserialized->getFilters()[1]->getType());
    }
}
