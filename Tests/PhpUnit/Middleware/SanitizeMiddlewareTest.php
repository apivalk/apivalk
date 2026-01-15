<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Middleware\SanitizeMiddleware;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Parameter\Parameter;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class SanitizeMiddlewareTest extends TestCase
{
    public function testSanitize(): void
    {
        $middleware = new SanitizeMiddleware();
        
        $body = new ParameterBag();
        $body->set(new Parameter('html', '<script>alert("xss")</script>', '<script>alert("xss")</script>'));
        $body->set(new Parameter('num', 123, 123));
        
        $query = new ParameterBag();
        $query->set(new Parameter('q', '<b>hi</b>', '<b>hi</b>'));
        
        $path = new ParameterBag();
        $path->set(new Parameter('slug', 'my-slug"', 'my-slug"'));

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('body')->willReturn($body);
        $request->method('query')->willReturn($query);
        $request->method('path')->willReturn($path);

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);

        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $body->html);
        $this->assertEquals(123, $body->num);
        $this->assertEquals('&lt;b&gt;hi&lt;/b&gt;', $query->q);
        $this->assertEquals('my-slug&quot;', $path->slug);
    }
}
