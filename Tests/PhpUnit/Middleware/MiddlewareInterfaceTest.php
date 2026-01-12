<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Middleware\MiddlewareInterface;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class MiddlewareInterfaceTest extends TestCase
{
    public function testInterface(): void
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(
                ApivalkRequestInterface $request,
                AbstractApivalkController $controller,
                callable $next
            ): AbstractApivalkResponse {
                return $next($request);
            }
        };

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }
}
