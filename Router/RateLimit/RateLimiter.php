<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\RateLimit;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;

class RateLimiter
{
    /** @var CacheInterface */
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function allow(RateLimitInterface $rateLimit, RateLimitContext $rateLimitContext): RateLimitResult
    {
        $cacheKey = $rateLimit->getKey($rateLimitContext);

        $cacheItem = $this->cache->get($cacheKey);
        if ($cacheItem === null) {
            $cacheItem = new CacheItem($cacheKey, 1, $rateLimit->getWindowInSeconds());
        } else {
            $cacheItem->setValue($cacheItem->getValue() + 1);
        }

        $this->cache->set($cacheItem);

        return new RateLimitResult(
            $rateLimit->getName(),
            $rateLimit->getMaxAttempts(),
            $rateLimit->getMaxAttempts() - $cacheItem->getValue(),
            $rateLimit->getWindowInSeconds(),
            $cacheItem->getExpiresAt() !== null ? $cacheItem->getExpiresAt()->getTimestamp() : null
        );
    }
}
