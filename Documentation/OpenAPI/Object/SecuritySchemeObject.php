<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class SecuritySchemeObject
 *
 * @see     https://swagger.io/specification/#security-scheme-object
 * @see     https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.0.4.md#security-scheme-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class SecuritySchemeObject implements ObjectInterface
{
    /** @var string */
    private $type;
    /** @var string|null */
    private $description;
    /** @var string */
    private $name;
    /** @var null|string */
    private $in;
    /** @var null|string */
    private $scheme;
    /** @var null|string */
    private $bearerFormat;
    /** @var OAuthFlowsObject|null */
    private $flows;
    /** @var null|string */
    private $openIdConnectUrl;

    public function __construct(
        string $type,
        string $name,
        ?string $description,
        ?string $in,
        ?string $scheme,
        ?string $bearerFormat,
        ?OAuthFlowsObject $flows,
        ?string $openIdConnectUrl
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->description = $description;
        $this->in = $in;
        $this->scheme = $scheme;
        $this->bearerFormat = $bearerFormat;
        $this->flows = $flows;
        $this->openIdConnectUrl = $openIdConnectUrl;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIn(): ?string
    {
        return $this->in;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function getBearerFormat(): ?string
    {
        return $this->bearerFormat;
    }

    public function getFlows(): ?OAuthFlowsObject
    {
        return $this->flows;
    }

    public function getOpenIdConnectUrl(): ?string
    {
        return $this->openIdConnectUrl;
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'type' => $this->type,
                'description' => $this->description,
                'name' => $this->name,
                'in' => $this->in,
                'scheme' => $this->scheme,
                'bearerFormat' => $this->bearerFormat,
                'flows' => $this->flows !== null ? array_filter($this->flows->toArray()) : null,
                'openIdConnectUrl' => $this->openIdConnectUrl,
            ]
        );
    }
}
