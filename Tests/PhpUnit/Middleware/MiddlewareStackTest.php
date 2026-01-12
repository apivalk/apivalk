<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Middleware\MiddlewareInterface;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;

class MiddlewareStackTest extends TestCase
{
    public function testHandleWithNoMiddlewares(): void
    {
        $stack = new MiddlewareStack();
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);

        $controller = $this->createMock(AbstractApivalkController::class);
        $controller->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willReturn($response);

        $request->expects($this->once())
            ->method('getRateLimitResult')
            ->willReturn(null);

        $response->expects($this->once())
            ->method('addHeaders')
            ->with([]);

        $result = $stack->handle($request, $controller);

        $this->assertSame($response, $result);
    }

    public function testHandleWithMiddlewares(): void
    {
        $stack = new MiddlewareStack();
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);
        $controller = $this->createMock(AbstractApivalkController::class);

        $executionOrder = [];

        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->once())
            ->method('process')
            ->willReturnCallback(function ($req, $ctrl, $next) use (&$executionOrder) {
                $executionOrder[] = 'middleware1_before';
                $res = $next($req);
                $executionOrder[] = 'middleware1_after';
                return $res;
            });

        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())
            ->method('process')
            ->willReturnCallback(function ($req, $ctrl, $next) use (&$executionOrder) {
                $executionOrder[] = 'middleware2_before';
                $res = $next($req);
                $executionOrder[] = 'middleware2_after';
                return $res;
            });

        $stack->add($middleware1);
        $stack->add($middleware2);

        $controller->expects($this->once())
            ->method('__invoke')
            ->willReturnCallback(function ($req) use (&$executionOrder, $response) {
                $executionOrder[] = 'controller';
                return $response;
            });

        $request->method('getRateLimitResult')->willReturn(null);

        $result = $stack->handle($request, $controller);

        $this->assertSame($response, $result);
        $this->assertEquals([
            'middleware1_before',
            'middleware2_before',
            'controller',
            'middleware2_after',
            'middleware1_after'
        ], $executionOrder);
    }

    public function testHandleAddsRateLimitHeaders(): void
    {
        $stack = new MiddlewareStack();
        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);
        $controller = $this->createMock(AbstractApivalkController::class);

        $rateLimitResult = new RateLimitResult('test', 100, 50, 60, 123456789);
        $expectedHeaders = $rateLimitResult->toHeaderArray();

        $request->method('getRateLimitResult')->willReturn($rateLimitResult);
        $controller->method('__invoke')->willReturn($response);

        $response->expects($this->once())
            ->method('addHeaders')
            ->with($expectedHeaders);

        $stack->handle($request, $controller);
    }
}
