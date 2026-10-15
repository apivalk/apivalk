<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Renderer;

use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\FileApivalkResponse;

final class DelegatingRenderer implements RendererInterface
{
    private RendererInterface $defaultRenderer;
    private RendererInterface $fileRenderer;

    public function __construct(RendererInterface $defaultRenderer, ?RendererInterface $fileRenderer = null)
    {
        $this->defaultRenderer = $defaultRenderer;
        $this->fileRenderer = $fileRenderer ?? new FileStreamRenderer();
    }

    public function render(AbstractApivalkResponse $response): void
    {
        if ($response instanceof FileApivalkResponse) {
            $this->fileRenderer->render($response);

            return;
        }

        $this->defaultRenderer->render($response);
    }
}
