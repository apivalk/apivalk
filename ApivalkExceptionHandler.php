<?php

declare(strict_types=1);

namespace apivalk\apivalk;

use apivalk\apivalk\Http\Renderer\JsonRenderer;
use apivalk\apivalk\Http\Response\InternalServerErrorApivalkResponse;

class ApivalkExceptionHandler
{
    public static function handle(\Throwable $t): void
    {
        $response = new InternalServerErrorApivalkResponse();

        (new JsonRenderer())->render($response);
    }
}
