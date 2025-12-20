<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
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
        /** @var class-string<AbstractApivalkController> $controllerClass */
        $controllerClass = \get_class($controller);
        $next = $controller;

        foreach (array_reverse($this->middlewares) as $middleware) {
            $next = static function (ApivalkRequestInterface $request) use ($middleware, $controllerClass, $next) {
                return $middleware->process($request, $controllerClass, $next);
            };
        }

        return $next($request);
    }
}
