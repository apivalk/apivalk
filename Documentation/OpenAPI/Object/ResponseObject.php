<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class ResponseObject
 *
 * @see     https://swagger.io/specification/#response-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class ResponseObject implements ObjectInterface
{
    private ?string $description;
    /** @var array<string, HeaderObject> */
    private array $headers;
    private ?MediaTypeObject $content;
    private int $statusCode;

    public function __construct(
        int $statusCode,
        ?MediaTypeObject $content = null,
        ?string $description = null,
        array $headers = []
    ) {
        $this->statusCode = $statusCode;
        $this->description = $description;
        $this->headers = $headers;
        $this->content = $content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getContent(): ?MediaTypeObject
    {
        return $this->content;
    }

    public function toArray(): array
    {
        $headers = array_map(static fn($headerObject) => $headerObject->toArray(), $this->headers);

        $body = array_filter(
            [
                'headers' => $headers,
                'content' => $this->content !== null ? array_filter($this->content->toArray()) : null,
            ]
        );

        $body['description'] = $this->description ?? '';

        return [$this->statusCode => $body];
    }
}
