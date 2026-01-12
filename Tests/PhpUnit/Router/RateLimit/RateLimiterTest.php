<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\RateLimit;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Router\RateLimit\RateLimitContext;
use apivalk\apivalk\Router\RateLimit\RateLimiter;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    private $cache;
    private $rateLimiter;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->rateLimiter = new RateLimiter($this->cache);
    }

    public function testAllowNewRequest(): void
    {
        $rateLimit = $this->createMock(RateLimitInterface::class);
        $rateLimit->method('getKey')->willReturn('key');
        $rateLimit->method('getName')->willReturn('test');
        $rateLimit->method('getMaxAttempts')->willReturn(10);
        $rateLimit->method('getWindowInSeconds')->willReturn(60);

        $context = $this->createMock(RateLimitContext::class);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn(null);

        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (CacheItem $item) {
                return $item->getKey() === 'key' && $item->getValue() === 1;
            }));

        $result = $this->rateLimiter->allow($rateLimit, $context);

        $this->assertInstanceOf(RateLimitResult::class, $result);
        $this->assertEquals(9, $result->getRemaining());
    }

    public function testAllowExistingRequest(): void
    {
        $rateLimit = $this->createMock(RateLimitInterface::class);
        $rateLimit->method('getKey')->willReturn('key');
        $rateLimit->method('getName')->willReturn('test');
        $rateLimit->method('getMaxAttempts')->willReturn(10);
        $rateLimit->method('getWindowInSeconds')->willReturn(60);

        $context = $this->createMock(RateLimitContext::class);

        $cacheItem = new CacheItem('key', 1, 60);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('key')
            ->willReturn($cacheItem);

        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (CacheItem $item) {
                return $item->getKey() === 'key' && $item->getValue() === 2;
            }));

        $result = $this->rateLimiter->allow($rateLimit, $context);

        $this->assertEquals(8, $result->getRemaining());
    }
}
