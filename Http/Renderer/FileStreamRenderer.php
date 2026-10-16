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

        $stream = $response->getStream();

        try {
            $this->assertNothingWasOutputYet();

            foreach ($response->getHeaders() as $name => $value) {
                header(\sprintf('%s: %s', $name, $value));
            }

            http_response_code($response->getStatusCode());

            fpassthru($stream);
        } finally {
            fclose($stream);
        }
    }

    /**
     * Earlier output would end up in front of the file: the download is corrupt and, with
     * Content-Length set, the client cuts off its end. Failing here still allows an error response.
     */
    private function assertNothingWasOutputYet(): void
    {
        if (headers_sent($file, $line)) {
            throw new \LogicException(
                \sprintf('Cannot stream the file, headers were already sent by output in %s:%d', $file, $line)
            );
        }

        $bufferedBytes = ob_get_length();
        if ($bufferedBytes !== false && $bufferedBytes > 0) {
            throw new \LogicException(\sprintf(
                'Cannot stream the file, the output buffer already holds %d bytes: "%s"',
                $bufferedBytes,
                substr((string)ob_get_contents(), 0, 100)
            ));
        }
    }
}
