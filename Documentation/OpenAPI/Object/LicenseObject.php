<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class LicenseObject
 *
 * @see     https://swagger.io/specification/#license-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class LicenseObject implements ObjectInterface
{
    /** @var string */
    private $name;
    /** @var string|null */
    private $identifier;
    /** @var string|null */
    private $url;

    public function __construct(string $name, ?string $identifier, ?string $url)
    {
        $this->name = $name;
        $this->identifier = $identifier;
        $this->url = $url;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'identifier' => $this->identifier,
                'url' => $this->url
            ]
        );
    }
}
