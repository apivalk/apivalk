<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class ServerObject
 *
 * @see     https://swagger.io/specification/#server-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class ServerObject implements ObjectInterface
{
    private string $url;
    private ?string $description;

    public function __construct(string $url, ?string $description = null)
    {
        $this->url = $url;
        $this->description = $description;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'description' => $this->description
        ];
    }
}
