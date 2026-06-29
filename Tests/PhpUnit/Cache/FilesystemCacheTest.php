<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Cache;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Cache\FilesystemCache;
use apivalk\apivalk\Cache\CacheItem;

class FilesystemCacheTest extends TestCase
{
    private $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/apivalk_cache_test_' . uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->cacheDir)) {
            $files = glob($this->cacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->cacheDir);
        }
    }

    public function testConstructorCreatesDirectory(): void
    {
        new FilesystemCache($this->cacheDir);
        $this->assertDirectoryExists($this->cacheDir);
    }

    public function testSetAndGet(): void
    {
        $cache = new FilesystemCache($this->cacheDir, 300);
        $item = new CacheItem('test_key', 'test_value');

        $this->assertEquals(300, $cache->getDefaultCacheLifetime());
        $this->assertTrue($cache->set($item));
        $retrieved = $cache->get('test_key');

        $this->assertInstanceOf(CacheItem::class, $retrieved);
        $this->assertEquals('test_value', $retrieved->getValue());
    }

    public function testGetNonExistent(): void
    {
        $cache = new FilesystemCache($this->cacheDir);
        $this->assertNull($cache->get('non_existent'));
    }

    public function testHas(): void
    {
        $cache = new FilesystemCache($this->cacheDir);
        $item = new CacheItem('test_key', 'test_value');

        $this->assertFalse($cache->has('test_key'));
        $cache->set($item);
        $this->assertTrue($cache->has('test_key'));
    }

    public function testDelete(): void
    {
        $cache = new FilesystemCache($this->cacheDir);
        $item = new CacheItem('test_key', 'test_value');

        $cache->set($item);
        $this->assertTrue($cache->has('test_key'));

        $this->assertTrue($cache->delete('test_key'));
        $this->assertFalse($cache->has('test_key'));
    }

    public function testClear(): void
    {
        $cache = new FilesystemCache($this->cacheDir);
        $cache->set(new CacheItem('key1', 'val1'));
        $cache->set(new CacheItem('key2', 'val2'));

        $this->assertTrue($cache->has('key1'));
        $this->assertTrue($cache->has('key2'));

        $cache->clear();

        $this->assertFalse($cache->has('key1'));
        $this->assertFalse($cache->has('key2'));
    }

    public function testDeleteReturnsTrueWhenFileAlreadyGone(): void
    {
        $cache = new FilesystemCache($this->cacheDir);

        $this->assertTrue($cache->delete('never_existed'));
    }

    public function testDeleteSurvivesHandlerThatPromotesWarningToException(): void
    {
        $cache = new FilesystemCache($this->cacheDir);
        $cache->set(new CacheItem('racy_key', 'value'));

        $reflection = new \ReflectionMethod(FilesystemCache::class, 'getCacheFilePath');
        $reflection->setAccessible(true);
        $path = $reflection->invoke($cache, 'racy_key');

        // Simulate a concurrent process winning the unlink race after our guard would have passed.
        $this->assertFileExists($path);
        unlink($path);

        // Mirror CRMI's ErrorHandler: promote every PHP warning to an exception, ignoring @-suppression.
        set_error_handler(static function (int $errno, string $message): bool {
            throw new \ErrorException($message, 0, $errno);
        });

        try {
            $result = $cache->delete('racy_key');
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($result, 'delete() must report success when the file is already gone');
    }

    public function testClearSurvivesHandlerThatPromotesWarningToException(): void
    {
        $cache = new FilesystemCache($this->cacheDir);
        $cache->set(new CacheItem('key1', 'val1'));
        $cache->set(new CacheItem('key2', 'val2'));

        set_error_handler(static function (int $errno, string $message): bool {
            throw new \ErrorException($message, 0, $errno);
        });

        try {
            // Remove the files out from under clear() to mimic a concurrent deletion.
            foreach (glob($this->cacheDir . '/*.cache') as $file) {
                unlink($file);
            }
            $cache->clear();
        } finally {
            restore_error_handler();
        }

        $this->assertFalse($cache->has('key1'));
        $this->assertFalse($cache->has('key2'));
    }

    public function testTtlValidation(): void
    {
        $cache = new FilesystemCache($this->cacheDir);
        $createdAt = new \DateTime('2026-01-04T12:00:00Z');
        $item = new CacheItem('expired', 'value', 60, $createdAt);
        $cache->set($item);

        $this->assertFalse($cache->has('expired'));
        $this->assertNull($cache->get('expired'));
    }
}
