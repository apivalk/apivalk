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
    const TYPE_HTTP = 'http';
    const TYPE_API_KEY = 'apiKey';
    const TYPE_OAUTH2 = 'oauth2';
    const TYPE_OPEN_ID_CONNECT = 'openIdConnect';
    const TYPE_MUTUAL_TLS = 'mutualTLS';

    /** @var string */
    private $type;
    /** @var string */
    private $name;
    /** @var string|null */
    private $description;
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

    public static function http(
        string $name,
        string $scheme,
        ?string $description = null,
        ?string $bearerFormat = null
    ): self {
        return new self(self::TYPE_HTTP, $name, $description, null, $scheme, $bearerFormat, null, null);
    }

    public static function apiKey(
        string $name,
        string $in,
        ?string $description = null
    ): self {
        return new self(self::TYPE_API_KEY, $name, $description, $in, null, null, null, null);
    }

    public static function oauth2(
        string $name,
        OAuthFlowsObject $flows,
        ?string $description = null
    ): self {
        return new self(self::TYPE_OAUTH2, $name, $description, null, null, null, $flows, null);
    }

    public static function openIdConnect(
        string $name,
        string $openIdConnectUrl,
        ?string $description = null
    ): self {
        return new self(self::TYPE_OPEN_ID_CONNECT, $name, $description, null, null, null, null, $openIdConnectUrl);
    }

    public function getType(): string
    {
        return $this->type;
    }

    /** Used internally to key the securitySchemes map and match RouteAuthorization — not emitted in toArray(). */
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
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
        $base = array_filter(
            [
                'type' => $this->type,
                'description' => $this->description,
            ]
        );

        switch ($this->type) {
            case self::TYPE_API_KEY:
                return array_filter(
                    $base + [
                        'name' => $this->name,
                        'in' => $this->in,
                    ]
                );

            case self::TYPE_HTTP:
                return array_filter(
                    $base + [
                        'scheme' => $this->scheme,
                        'bearerFormat' => $this->bearerFormat,
                    ]
                );

            case self::TYPE_OAUTH2:
                return array_filter(
                    $base + [
                        'flows' => $this->flows !== null ? array_filter($this->flows->toArray()) : null,
                    ]
                );

            case self::TYPE_OPEN_ID_CONNECT:
                return array_filter(
                    $base + [
                        'openIdConnectUrl' => $this->openIdConnectUrl,
                    ]
                );

            default:
                return $base;
        }
    }
}
