<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Renderer;

use apivalk\apivalk\Http\Renderer\FileStreamRenderer;
use apivalk\apivalk\Http\Response\FileApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use PHPUnit\Framework\TestCase;

class FileStreamRendererTest extends TestCase
{
    public function testRenderStreamsFileContentAndClosesStream(): void
    {
        $response = FileApivalkResponse::fromContent('a,b', 'export.csv', 'text/csv');
        $stream = $response->getStream();

        ob_start();
        @(new FileStreamRenderer())->render($response);
        $output = ob_get_clean();

        $this->assertSame('a,b', $output);
        $this->assertFalse(\is_resource($stream));
    }

    public function testRenderThrowsForNonFileResponse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(NotFoundApivalkResponse::class);

        (new FileStreamRenderer())->render(new NotFoundApivalkResponse());
    }
}
