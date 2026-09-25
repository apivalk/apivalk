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

    private string $type;
    private string $name;
    private ?string $description;
    private ?string $in;
    private ?string $scheme;
    private ?string $bearerFormat;
    private ?OAuthFlowsObject $flows;
    private ?string $openIdConnectUrl;
    /** @var string[] */
    private array $audiences;

    /** @param string|string[] $audiences */
    public function __construct(
        string $type,
        string $name,
        ?string $description,
        ?string $in,
        ?string $scheme,
        ?string $bearerFormat,
        ?OAuthFlowsObject $flows,
        ?string $openIdConnectUrl,
        $audiences = []
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->description = $description;
        $this->in = $in;
        $this->scheme = $scheme;
        $this->bearerFormat = $bearerFormat;
        $this->flows = $flows;
        $this->openIdConnectUrl = $openIdConnectUrl;
        $this->audiences = self::normalizeAudiences($audiences);
    }

    /** @param string|string[] $audiences */
    public static function http(
        string $name,
        string $scheme,
        ?string $description = null,
        ?string $bearerFormat = null,
        $audiences = []
    ): self {
        return new self(self::TYPE_HTTP, $name, $description, null, $scheme, $bearerFormat, null, null, $audiences);
    }

    /** @param string|string[] $audiences */
    public static function apiKey(
        string $name,
        string $in,
        ?string $description = null,
        $audiences = []
    ): self {
        return new self(self::TYPE_API_KEY, $name, $description, $in, null, null, null, null, $audiences);
    }

    /** @param string|string[] $audiences */
    public static function oauth2(
        string $name,
        OAuthFlowsObject $flows,
        ?string $description = null,
        $audiences = []
    ): self {
        return new self(self::TYPE_OAUTH2, $name, $description, null, null, null, $flows, null, $audiences);
    }

    /** @param string|string[] $audiences */
    public static function openIdConnect(
        string $name,
        string $openIdConnectUrl,
        ?string $description = null,
        $audiences = []
    ): self {
        return new self(
            self::TYPE_OPEN_ID_CONNECT,
            $name,
            $description,
            null,
            null,
            null,
            null,
            $openIdConnectUrl,
            $audiences
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Keys the securitySchemes map, matches RouteAuthorization at runtime and resolves the scheme
     * in the SecuritySchemeCollection — not emitted in toArray().
     */
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

    /** @return string[] */
    public function getAudiences(): array
    {
        return $this->audiences;
    }

    public function toArray(): array
    {
        $base = array_filter(
            [
                'type' => $this->type,
                'description' => $this->description,
                'x-audience' => $this->audiences,
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

    /**
     * @param string|string[] $audiences
     *
     * @return string[]
     */
    private static function normalizeAudiences($audiences): array
    {
        if (\is_string($audiences)) {
            return [$audiences];
        }

        return \array_values($audiences);
    }
}
