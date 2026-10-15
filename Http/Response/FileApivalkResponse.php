<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\FileResponseDocumentation;

class FileApivalkResponse extends AbstractApivalkResponse
{
    private const DEFAULT_MIME_TYPE = 'application/octet-stream';

    /** @var resource */
    private $stream;

    /**
     * @param resource $stream
     */
    final public function __construct(
        $stream,
        string $fileName,
        string $mimeType = self::DEFAULT_MIME_TYPE,
        bool $inline = false
    ) {
        if (!\is_resource($stream)) {
            throw new \InvalidArgumentException(
                \sprintf('Expected a stream resource for file "%s", got %s', $fileName, \gettype($stream))
            );
        }

        $this->stream = $stream;

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => self::buildContentDisposition($fileName, $inline),
            'X-Content-Type-Options' => 'nosniff',
        ];

        $remainingBytes = self::remainingBytes($stream);
        if ($remainingBytes !== null) {
            $headers['Content-Length'] = $remainingBytes;
        }

        $this->addHeaders($headers);
    }

    /**
     * Without an explicit mime type it is sniffed from the file content, not the extension.
     */
    public static function fromPath(
        string $filePath,
        ?string $fileName = null,
        ?string $mimeType = null,
        bool $inline = false
    ): self {
        $stream = @fopen($filePath, 'rb');
        if ($stream === false) {
            throw new \RuntimeException(\sprintf('Cannot open file "%s" for download', $filePath));
        }

        $mimeType = $mimeType ?? (new \finfo(\FILEINFO_MIME_TYPE))->file($filePath);

        return new static($stream, $fileName ?? basename($filePath), $mimeType ?: self::DEFAULT_MIME_TYPE, $inline);
    }

    public static function fromContent(
        string $content,
        string $fileName,
        ?string $mimeType = null,
        bool $inline = false
    ): self {
        $stream = fopen('php://temp', 'r+b');
        fwrite($stream, $content);
        rewind($stream);

        $mimeType = $mimeType ?? (new \finfo(\FILEINFO_MIME_TYPE))->buffer($content);

        return new static($stream, $fileName, $mimeType ?: self::DEFAULT_MIME_TYPE, $inline);
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_200_OK;
    }

    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        return new FileResponseDocumentation(self::DEFAULT_MIME_TYPE, 'File download');
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [];
    }

    /** @return resource */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * RFC 6266: a quoted ASCII fallback for old clients plus filename* for UTF-8 names.
     * Quotes and backslashes are stripped because they would end the quoted parameter early.
     */
    private static function buildContentDisposition(string $fileName, bool $inline): string
    {
        $fileName = str_replace(["\r", "\n", '"', '\\'], '', $fileName);
        $asciiFallback = preg_replace('/[^\x20-\x7E]/', '_', $fileName);

        return \sprintf(
            '%s; filename="%s"; filename*=UTF-8\'\'%s',
            $inline ? 'inline' : 'attachment',
            $asciiFallback,
            rawurlencode($fileName)
        );
    }

    /** @param resource $stream */
    private static function remainingBytes($stream): ?int
    {
        $stat = fstat($stream);
        $position = ftell($stream);
        // Pipes and sockets report no size, then Content-Length is skipped and the server chunks.
        if ($stat === false || $stat['size'] === 0 || $position === false) {
            return null;
        }

        return $stat['size'] - $position;
    }
}
