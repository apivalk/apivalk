<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Renderer;

use apivalk\apivalk\Http\Renderer\FileStreamRenderer;
use apivalk\apivalk\Http\Response\FileApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use PHPUnit\Framework\TestCase;

/**
 * Rendering tests run in a separate process: PHPUnit's own output already counts as sent headers.
 */
class FileStreamRendererTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testRenderStreamsFileContentAndClosesStream(): void
    {
        $response = FileApivalkResponse::fromContent('a,b', 'export.csv', 'text/csv');
        $stream = $response->getStream();

        ob_start();
        (new FileStreamRenderer())->render($response);
        $output = ob_get_clean();

        $this->assertSame('a,b', $output);
        $this->assertFalse(\is_resource($stream));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRenderThrowsAndClosesStreamWhenOutputIsAlreadyBuffered(): void
    {
        $response = FileApivalkResponse::fromContent('a,b', 'export.csv', 'text/csv');
        $stream = $response->getStream();

        ob_start();
        echo 'Notice: stray output';

        try {
            (new FileStreamRenderer())->render($response);
            $this->fail('Expected a LogicException for buffered output');
        } catch (\LogicException $exception) {
            $this->assertStringContainsString('20 bytes', $exception->getMessage());
            $this->assertStringContainsString('Notice: stray output', $exception->getMessage());
        } finally {
            $this->assertSame('Notice: stray output', ob_get_clean());
        }

        $this->assertFalse(\is_resource($stream));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRenderThrowsAndClosesStreamWhenHeadersWereAlreadySent(): void
    {
        $response = FileApivalkResponse::fromContent('a,b', 'export.csv', 'text/csv');
        $stream = $response->getStream();

        // The child process runs inside PHPUnit's own output buffer, only unbuffered output sends headers.
        $bufferLevel = ob_get_level();
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        echo 'output without buffering';

        try {
            (new FileStreamRenderer())->render($response);
            $this->fail('Expected a LogicException for sent headers');
        } catch (\LogicException $exception) {
            $this->assertStringContainsString('headers were already sent', $exception->getMessage());
        } finally {
            for ($level = 0; $level < $bufferLevel; $level++) {
                ob_start();
            }
        }

        $this->assertFalse(\is_resource($stream));
    }

    public function testRenderThrowsForNonFileResponse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(NotFoundApivalkResponse::class);

        (new FileStreamRenderer())->render(new NotFoundApivalkResponse());
    }
}
