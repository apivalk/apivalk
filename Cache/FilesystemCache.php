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
        $path = $this->getCacheFilePath($cacheItem->getKey());
        $tmpPath = \sprintf('%s.%d.tmp', $path, getmypid());

        if (file_put_contents($tmpPath, $cacheItem->toJson()) === false) {
            return false;
        }

        if (!rename($tmpPath, $path)) {
            @unlink($tmpPath);
            return false;
        }

        return true;
    }

    public function delete(string $key): bool
    {
        $path = $this->getCacheFilePath($key);

        try {
            if (@unlink($path)) {
                return true;
            }
        } catch (\Throwable $exception) {
            // A strict error handler may promote unlink()'s warning to an exception when a
            // concurrent process removed the file first; the desired state is still reached.
        }

        return !file_exists($path);
    }

    public function clear(): void
    {
        foreach (glob(\sprintf('%s/*.cache', $this->cacheDir)) as $file) {
            try {
                @unlink($file);
            } catch (\Throwable $exception) {
                // Another process may have removed this file concurrently; nothing left to do.
            }
        }
    }

    private function isTtlValid(CacheItem $cacheItem): bool
    {
        $ttl = $cacheItem->getTtl();
        if ($ttl === null || $cacheItem->getExpiresAt() === null) {
            return true;
        }

        $expiresAt = $cacheItem->getExpiresAt()->getTimestamp();

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
