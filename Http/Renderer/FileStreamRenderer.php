<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Renderer;

use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\FileApivalkResponse;

final class FileStreamRenderer implements RendererInterface
{
    public function render(AbstractApivalkResponse $response): void
    {
        if (!$response instanceof FileApivalkResponse) {
            throw new \InvalidArgumentException(\sprintf(
                '%s can only render %s, got %s',
                self::class,
                FileApivalkResponse::class,
                \get_class($response)
            ));
        }

        foreach ($response->getHeaders() as $name => $value) {
            header(\sprintf('%s: %s', $name, $value));
        }

        http_response_code($response->getStatusCode());

        $stream = $response->getStream();
        fpassthru($stream);
        fclose($stream);
    }
}
