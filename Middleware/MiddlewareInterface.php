<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

interface MiddlewareInterface
{
    public function process(
        ApivalkRequestInterface $request,
        string $controllerClass,
        callable $next
    ): AbstractApivalkResponse;
}
