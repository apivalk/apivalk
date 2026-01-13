<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Util;

use apivalk\apivalk\Util\IpResolver;
use PHPUnit\Framework\TestCase;

class IpResolverTest extends TestCase
{
    private $savedServer;

    protected function setUp(): void
    {
        $this->savedServer = $_SERVER;
        $_SERVER = [];
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->savedServer;
    }

    public function testGetClientIpFromCloudflare(): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '1.1.1.1';
        $this->assertEquals('1.1.1.1', IpResolver::getClientIp());
    }

    public function testGetClientIpFromXForwardedFor(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.1.1, 8.8.8.8';
        $this->assertEquals('1.1.1.1', IpResolver::getClientIp());
    }

    public function testGetClientIpFromXRealIp(): void
    {
        $_SERVER['HTTP_X_REAL_IP'] = '1.1.1.1';
        $this->assertEquals('1.1.1.1', IpResolver::getClientIp());
    }

    public function testGetClientIpFromRemoteAddr(): void
    {
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        $this->assertEquals('1.1.1.1', IpResolver::getClientIp());
    }

    public function testGetClientIpIgnoresPrivateIps(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.1.1, 1.1.1.1';
        $this->assertEquals('1.1.1.1', IpResolver::getClientIp());
    }

    public function testGetClientIpReturnsNullIfNoPublicIp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $this->assertNull(IpResolver::getClientIp());
    }

    public function testExtractIpsWithForSyntax(): void
    {
        $_SERVER['HTTP_FORWARDED'] = 'for=1.1.1.1;proto=https, for=2.2.2.2';
        $this->assertEquals('1.1.1.1', IpResolver::getClientIp());
    }
}
