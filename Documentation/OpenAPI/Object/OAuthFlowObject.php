<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class OAuthFlowObject
 *
 * @see     https://swagger.io/specification/#oauth-flow-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class OAuthFlowObject implements ObjectInterface
{
    private string $authorizationUrl;
    private string $tokenUrl;
    private ?string $refreshUrl;
    /** @var array<string, string> */
    private array $scopes;

    public function __construct(
        string $authorizationUrl,
        string $tokenUrl,
        ?string $refreshUrl = null,
        array $scopes = []
    ) {
        $this->authorizationUrl = $authorizationUrl;
        $this->tokenUrl = $tokenUrl;
        $this->refreshUrl = $refreshUrl;
        $this->scopes = $scopes;
    }

    public function getAuthorizationUrl(): string
    {
        return $this->authorizationUrl;
    }

    public function getTokenUrl(): string
    {
        return $this->tokenUrl;
    }

    public function getRefreshUrl(): ?string
    {
        return $this->refreshUrl;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function toArray(): array
    {
        $result = [
            'authorizationUrl' => $this->authorizationUrl,
            'tokenUrl' => $this->tokenUrl,
            'scopes' => $this->scopes
        ];

        if ($this->refreshUrl !== null) {
            $result['refreshUrl'] = $this->refreshUrl;
        }

        return $result;
    }
}
