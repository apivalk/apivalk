<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Cache;

use apivalk\apivalk\Cache\CacheInterface;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Cache\CacheItem;

class CacheItemTest extends TestCase
{
    public function testGetters(): void
    {
        $key = 'test_key';
        $value = ['foo' => 'bar'];
        $ttl = 3600;
        $createdAt = new \DateTime('2026-01-04T12:48:54Z');

        $item = new CacheItem($key, $value, $ttl, $createdAt);

        $this->assertEquals($key, $item->getKey());
        $this->assertEquals($value, $item->getValue());
        $this->assertEquals($ttl, $item->getTtl());
        $this->assertEquals($createdAt, $item->getCreatedAt());
    }

    public function testDefaultCreatedAt(): void
    {
        $item = new CacheItem('key', 'value');
        $this->assertNotNull($item->getCreatedAt());
        $this->assertRegExp(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/',
            $item->getCreatedAt()->format(CacheInterface::CREATED_AT_FORMAT)
        );
    }

    public function testJsonSerialization(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        $ttl = 100;
        $createdAt = new \DateTime('2026-01-04T12:48:54Z');

        $item = new CacheItem($key, $value, $ttl, $createdAt);
        $json = $item->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertEquals($key, $decoded['key']);
        $this->assertEquals($value, $decoded['value']);
        $this->assertEquals($ttl, $decoded['ttl']);
        $this->assertEquals($createdAt->format(CacheInterface::CREATED_AT_FORMAT), $decoded['createdAt']);

        $newItem = CacheItem::byJson($json);
        $this->assertInstanceOf(CacheItem::class, $newItem);
        $this->assertEquals($key, $newItem->getKey());
        $this->assertEquals($value, $newItem->getValue());
        $this->assertEquals($ttl, $newItem->getTtl());
        $this->assertEquals($createdAt, $newItem->getCreatedAt());
    }

    public function testByJsonReturnsNullOnInvalidData(): void
    {
        $this->assertNull(CacheItem::byJson('invalid json'));
        $this->assertNull(CacheItem::byJson('{"key":"missing_fields"}'));
    }
}
