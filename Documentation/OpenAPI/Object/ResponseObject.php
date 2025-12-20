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
    /** @var string|null */
    private $description;
    /** @var array<string, HeaderObject> */
    private $headers;
    /** @var MediaTypeObject|null */
    private $content;
    /** @var int */
    private $statusCode;

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
        return [
            $this->statusCode => array_filter(
                [
                    'description' => $this->description ?? '',
                    'headers' => $this->headers,
                    'content' => $this->content !== null ? array_filter($this->content->toArray()) : null,
                ]
            )
        ];
    }
}
