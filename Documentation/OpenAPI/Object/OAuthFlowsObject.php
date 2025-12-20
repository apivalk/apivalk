<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class OAuthFlowsObject
 *
 * @see     https://swagger.io/specification/#oauth-flows-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class OAuthFlowsObject
{
    /** @var OAuthFlowObject|null */
    private $implicit;
    /** @var OAuthFlowObject|null */
    private $password;
    /** @var OAuthFlowObject|null */
    private $clientCredentials;
    /** @var OAuthFlowObject|null */
    private $authorizationCode;

    public function __construct(
        ?OAuthFlowObject $implicit,
        ?OAuthFlowObject $password,
        ?OAuthFlowObject $clientCredentials,
        ?OAuthFlowObject $authorizationCode
    ) {
        $this->implicit = $implicit;
        $this->password = $password;
        $this->clientCredentials = $clientCredentials;
        $this->authorizationCode = $authorizationCode;
    }

    public function getImplicit(): ?OAuthFlowObject
    {
        return $this->implicit;
    }

    public function getPassword(): ?OAuthFlowObject
    {
        return $this->password;
    }

    public function getClientCredentials(): ?OAuthFlowObject
    {
        return $this->clientCredentials;
    }

    public function getAuthorizationCode(): ?OAuthFlowObject
    {
        return $this->authorizationCode;
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'implicit' =>
                    $this->implicit !== null ? array_filter($this->implicit->toArray()) : null,
                'password' =>
                    $this->password !== null ? array_filter($this->password->toArray()) : null,
                'clientCredentials' =>
                    $this->clientCredentials !== null ? array_filter($this->clientCredentials->toArray()) : null,
                'authorizationCode' =>
                    $this->authorizationCode !== null ? array_filter($this->authorizationCode->toArray()) : null,
            ]
        );
    }
}
