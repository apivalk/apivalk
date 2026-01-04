<?php

declare(strict_types=1);

namespace apivalk\apivalk\Cache;

class FilesystemCache implements CacheInterface
{
    /** @var string */
    private $cacheDir;
    /** @var int */
    private $defaultCacheLifetime;

    public function __construct(string $cacheDir, int $defaultCacheLifetime = 600)
    {
        $this->cacheDir = $cacheDir;
        $this->defaultCacheLifetime = $defaultCacheLifetime;

        if (!is_dir($this->cacheDir)
            && !mkdir($concurrentDirectory = $this->cacheDir, 0777, true)) {
            throw new \RuntimeException(
                \sprintf('Directory "%s" could not be created or does not exist', $concurrentDirectory)
            );
        }
    }

    public function getDefaultCacheLifetime(): int
    {
        return $this->defaultCacheLifetime;
    }

    private function getCacheFilePath(string $key): string
    {
        return \sprintf('%s/%s.cache', $this->cacheDir, hash('sha256', $key));
    }

    public function get(string $key): ?CacheItem
    {
        if (!file_exists($this->getCacheFilePath($key))) {
            return null;
        }

        $cacheItem = CacheItem::byJson(file_get_contents($this->getCacheFilePath($key)));
        if ($cacheItem === null) {
            return null;
        }

        $ttlValid = $this->isTtlValid($cacheItem);
        if (!$ttlValid) {
            return null;
        }

        return $cacheItem;
    }

    public function set(CacheItem $cacheItem): bool
    {
        return (bool)file_put_contents(
            $this->getCacheFilePath($cacheItem->getKey()),
            $cacheItem->toJson()
        );
    }

    public function delete(string $key): bool
    {
        if (!file_exists($this->getCacheFilePath($key))) {
            return true;
        }

        return unlink($this->getCacheFilePath($key));
    }

    public function clear(): void
    {
        foreach (glob(\sprintf('%s/*.cache', $this->cacheDir)) as $file) {
            unlink($file);
        }
    }

    private function isTtlValid(CacheItem $cacheItem): bool
    {
        $ttl = $cacheItem->getTtl();
        if ($ttl === null) {
            return true;
        }

        $expiresAt = $cacheItem->getCreatedAt()->getTimestamp() + $ttl;

        if ($expiresAt < time()) {
            $this->delete($cacheItem->getKey());
            return false;
        }

        return true;
    }


    public function has(string $key): bool
    {
        if (!file_exists($this->getCacheFilePath($key))) {
            return false;
        }

        $cacheItem = CacheItem::byJson(file_get_contents($this->getCacheFilePath($key)));
        if ($cacheItem === null) {
            return false;
        }

        return $this->isTtlValid($cacheItem);
    }
}
