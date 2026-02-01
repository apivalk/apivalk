<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router;

use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Router\RouteJsonSerializer;
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
}
