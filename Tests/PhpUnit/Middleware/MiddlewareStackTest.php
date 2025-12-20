<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Middleware\MiddlewareInterface;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class MiddlewareStackTest extends TestCase
{
    public function testStackExecutionOrder(): void
    {
        $stack = new MiddlewareStack();
        $executionOrder = [];

        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->method('process')->willReturnCallback(function ($request, $class, $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware1_before';
            $response = $next($request);
            $executionOrder[] = 'middleware1_after';
            return $response;
        });

        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->method('process')->willReturnCallback(function ($request, $class, $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware2_before';
            $response = $next($request);
            $executionOrder[] = 'middleware2_after';
            return $response;
        });

        $stack->add($middleware1);
        $stack->add($middleware2);

        $request = $this->createMock(ApivalkRequestInterface::class);
        $response = $this->createMock(AbstractApivalkResponse::class);

        $controller = new class($response, $executionOrder) {
            private $resp;
            private $order;
            public function __construct($resp, &$order) { $this->resp = $resp; $this->order = &$order; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse {
                $this->order[] = 'controller';
                return $this->resp;
            }
        };

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
}
