<?php

declare(strict_types=1);

namespace apivalk\apivalk\Cache;

interface CacheInterface
{
    public const CREATED_AT_FORMAT = 'Y-m-d\TH:i:s\Z';

    public function get(string $key): ?CacheItem;

    public function set(CacheItem $cacheItem): bool;

    public function delete(string $key): bool;

    public function clear(): void;

    public function has(string $key): bool;

    public function getDefaultCacheLifetime(): int;
}
