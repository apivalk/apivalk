<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\RateLimit;

use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use PHPUnit\Framework\TestCase;

class RateLimitResultTest extends TestCase
{
    public function testGetters(): void
    {
        $result = new RateLimitResult('test', 100, 99, 60, 1234567890);

        $this->assertEquals('test', $result->getName());
        $this->assertEquals(100, $result->getLimit());
        $this->assertEquals(99, $result->getRemaining());
        $this->assertEquals(60, $result->getWindowSeconds());
        $this->assertEquals(1234567890, $result->getResetAt());
    }

    public function testToHeaderArray(): void
    {
        $result = new RateLimitResult('test', 100, 99, 60, 1234567890);
        $headers = $result->toHeaderArray();

        $this->assertEquals(100, $headers['X-RateLimit-Limit']);
        $this->assertEquals(99, $headers['X-RateLimit-Remaining']);
        $this->assertEquals(1234567890, $headers['X-RateLimit-Reset']);
        $this->assertArrayNotHasKey('Retry-After', $headers);
    }

    public function testToHeaderArrayWithNoRemaining(): void
    {
        $result = new RateLimitResult('test', 100, 0, 60, 1234567890);
        $headers = $result->toHeaderArray();

        $this->assertEquals(0, $headers['X-RateLimit-Remaining']);
        $this->assertEquals(1234567890, $headers['Retry-After']);
    }
}
