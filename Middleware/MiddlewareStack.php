<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;

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

        if ($request->getRateLimitResult() instanceof RateLimitResult) {
            $response->addHeaders($request->getRateLimitResult()->toHeaderArray());
        }

        $response->addHeaders(
            [
                'Content-Language' => $request->getLocale()->getTag(),
            ]
        );

        return $response;
    }
}
