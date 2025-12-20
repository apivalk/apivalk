<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Renderer;

use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

interface RendererInterface
{
    public function render(AbstractApivalkResponse $response): void;
}
