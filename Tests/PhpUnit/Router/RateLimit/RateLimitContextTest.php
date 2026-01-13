<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\RateLimit;

use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Router\RateLimit\RateLimitContext;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use PHPUnit\Framework\TestCase;

class RateLimitContextTest extends TestCase
{
    public function testGetters(): void
    {
        $authIdentity = $this->createMock(AbstractAuthIdentity::class);
        $context = new RateLimitContext('1.1.1.1', $authIdentity, '/test', 'GET');

        $this->assertEquals('1.1.1.1', $context->getIp());
        $this->assertSame($authIdentity, $context->getAuthIdentity());
        $this->assertEquals('/test', $context->getRoute());
        $this->assertEquals('GET', $context->getMethod());
    }

    public function testByRequest(): void
    {
        $route = $this->createMock(Route::class);
        $route->method('getUrl')->willReturn('/test');

        $authIdentity = $this->createMock(AbstractAuthIdentity::class);
        $method = $this->createMock(MethodInterface::class);
        $method->method('getName')->willReturn('POST');

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getIp')->willReturn('2.2.2.2');
        $request->method('getAuthIdentity')->willReturn($authIdentity);
        $request->method('getMethod')->willReturn($method);

        $context = RateLimitContext::byRequest($route, $request);

        $this->assertEquals('2.2.2.2', $context->getIp());
        $this->assertSame($authIdentity, $context->getAuthIdentity());
        $this->assertEquals('/test', $context->getRoute());
        $this->assertEquals('POST', $context->getMethod());
    }
}
