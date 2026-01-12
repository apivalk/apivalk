<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security;

class UserAuthIdentity extends AbstractAuthIdentity
{
    /** @var string */
    private $userId;
    /** @var ScopeInterface[] */
    private $grantedScopes;
    /** @var array<string, mixed> */
    private $claims;

    /**
     * @param string               $userId
     * @param ScopeInterface[]     $grantedScopes
     * @param array<string, mixed> $claims
     */
    public function __construct(string $userId, array $grantedScopes = [], array $claims = [])
    {
        $this->userId = $userId;
        $this->grantedScopes = $grantedScopes;
        $this->claims = $claims;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /** @return ScopeInterface[] */
    public function getGrantedScopes(): array
    {
        return $this->grantedScopes;
    }

    public function isAuthenticated(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /** @return mixed|null */
    public function getClaim(string $name)
    {
        return $this->claims[$name] ?? null;
    }
}
