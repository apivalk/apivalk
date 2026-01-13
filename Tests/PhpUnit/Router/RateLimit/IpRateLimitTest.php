<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\RateLimit;

use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\RateLimit\RateLimitContext;
use PHPUnit\Framework\TestCase;

class IpRateLimitTest extends TestCase
{
    public function testGetters(): void
    {
        $rateLimit = new IpRateLimit('test', 10, 60);

        $this->assertEquals('test', $rateLimit->getName());
        $this->assertEquals(10, $rateLimit->getMaxAttempts());
        $this->assertEquals(60, $rateLimit->getWindowInSeconds());
    }

    public function testGetKey(): void
    {
        $rateLimit = new IpRateLimit('test', 10, 60);
        $context = $this->createMock(RateLimitContext::class);
        $context->method('getIp')->willReturn('1.1.1.1');

        $this->assertEquals('rateLimit:test:1.1.1.1', $rateLimit->getKey($context));
    }
}
