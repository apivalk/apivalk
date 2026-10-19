<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use apivalk\apivalk\Documentation\FileResponseDocumentation;
use apivalk\apivalk\Http\Response\FileApivalkResponse;
use PHPUnit\Framework\TestCase;

class FileApivalkResponseTest extends TestCase
{
    private const PNG_BYTES = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR";

    public function testFromContentSetsDownloadHeaders(): void
    {
        $response = FileApivalkResponse::fromContent('a,b', 'export.csv', 'text/csv');

        $headers = $response->getHeaders();
        $this->assertSame('text/csv', $headers['Content-Type']);
        $this->assertSame('attachment; filename="export.csv"; filename*=UTF-8\'\'export.csv', $headers['Content-Disposition']);
        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        $this->assertSame(3, $headers['Content-Length']);
        $this->assertSame('a,b', stream_get_contents($response->getStream()));
    }

    public function testFromContentDetectsMimeTypeWhenNotGiven(): void
    {
        $response = FileApivalkResponse::fromContent(self::PNG_BYTES, 'image.png');

        $this->assertSame('image/png', $response->getHeaders()['Content-Type']);
    }

    public function testFromPathDetectsMimeTypeAndUsesBasenameAsFileName(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'apivalk');
        file_put_contents($filePath, self::PNG_BYTES);

        try {
            $response = FileApivalkResponse::fromPath($filePath);
            $headers = $response->getHeaders();

            $this->assertSame('image/png', $headers['Content-Type']);
            $this->assertStringContainsString(\sprintf('filename="%s"', basename($filePath)), $headers['Content-Disposition']);
            $this->assertSame(\strlen(self::PNG_BYTES), $headers['Content-Length']);
        } finally {
            unlink($filePath);
        }
    }

    public function testFromPathPrefersExplicitMimeTypeAndFileName(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'apivalk');
        file_put_contents($filePath, self::PNG_BYTES);

        try {
            $response = FileApivalkResponse::fromPath($filePath, 'logo.png', 'application/x-custom');

            $this->assertSame('application/x-custom', $response->getHeaders()['Content-Type']);
            $this->assertStringContainsString('filename="logo.png"', $response->getHeaders()['Content-Disposition']);
        } finally {
            unlink($filePath);
        }
    }

    public function testFromPathThrowsForMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot open file "/does/not/exist.pdf" for download');

        FileApivalkResponse::fromPath('/does/not/exist.pdf');
    }

    public function testConstructorThrowsForNonResource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a stream resource for file "export.csv", got string');

        new FileApivalkResponse('not a stream', 'export.csv');
    }

    public function testConstructorDefaultsToOctetStream(): void
    {
        $stream = fopen('php://temp', 'r+b');

        $response = new FileApivalkResponse($stream, 'blob.bin');

        $this->assertSame('application/octet-stream', $response->getHeaders()['Content-Type']);
    }

    public function testInlineDisposition(): void
    {
        $response = FileApivalkResponse::fromContent('%PDF', 'invoice.pdf', 'application/pdf', true);

        $this->assertStringStartsWith('inline; ', $response->getHeaders()['Content-Disposition']);
    }

    public function testFileNameIsSanitizedForContentDisposition(): void
    {
        $response = FileApivalkResponse::fromContent('x', "Rech\"nung\r\n\\ä.pdf", 'application/pdf');

        $this->assertSame(
            'attachment; filename="Rechnung__.pdf"; filename*=UTF-8\'\'Rechnung%C3%A4.pdf',
            $response->getHeaders()['Content-Disposition']
        );
    }

    public function testContentLengthCountsFromCurrentStreamPosition(): void
    {
        $stream = fopen('php://temp', 'r+b');
        fwrite($stream, 'header,body');
        fseek($stream, 7);

        $response = new FileApivalkResponse($stream, 'body.txt', 'text/plain');

        $this->assertSame(4, $response->getHeaders()['Content-Length']);
    }

    public function testContentLengthIsOmittedForEmptyStream(): void
    {
        $response = new FileApivalkResponse(fopen('php://temp', 'r+b'), 'empty.txt', 'text/plain');

        $this->assertArrayNotHasKey('Content-Length', $response->getHeaders());
    }

    public function testStatusCodeAndDocumentation(): void
    {
        $response = FileApivalkResponse::fromContent('x', 'x.txt', 'text/plain');

        $this->assertSame(200, $response::getStatusCode());
        $this->assertSame([], $response->toArray());

        $documentation = FileApivalkResponse::getDocumentation();
        $this->assertInstanceOf(FileResponseDocumentation::class, $documentation);
        $this->assertSame('application/octet-stream', $documentation->getMediaType());
    }
}
