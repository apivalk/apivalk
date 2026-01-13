<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class MiddlewareStack
{
    /** @var MiddlewareInterface[] */
    private $middlewares = [];

    public function add(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function handle(ApivalkRequestInterface $request, callable $controller): AbstractApivalkResponse
    {
        $next = $controller;

        foreach (array_reverse($this->middlewares) as $middleware) {
            $next = static function (ApivalkRequestInterface $request) use ($middleware, $controller, $next) {
                return $middleware->process($request, $controller, $next);
            };
        }

        $response = $next($request);
        $response->addHeaders(
            $request->getRateLimitResult() !== null ? $request->getRateLimitResult()->toHeaderArray() : []
        );

        return $response;
    }
}
