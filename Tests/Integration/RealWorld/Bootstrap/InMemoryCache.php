<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Bootstrap;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;

class InMemoryCache implements CacheInterface
{
    /** @var array<string, CacheItem> */
    private $items = [];

    public function get(string $key): ?CacheItem
    {
        if (!isset($this->items[$key])) {
            return null;
        }

        $item = $this->items[$key];
        $expiresAt = $item->getExpiresAt();

        if ($expiresAt !== null && new \DateTime('now', new \DateTimeZone('UTC')) > $expiresAt) {
            unset($this->items[$key]);
            return null;
        }

        return $item;
    }

    public function set(CacheItem $cacheItem): bool
    {
        $this->items[$cacheItem->getKey()] = $cacheItem;
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->items[$key]);
        return true;
    }

    public function clear(): void
    {
        $this->items = [];
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function getDefaultCacheLifetime(): int
    {
        return 3600;
    }
}
