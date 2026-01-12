<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\AuthIdentity;

use apivalk\apivalk\Security\ScopeInterface;

class GuestAuthIdentity extends AbstractAuthIdentity
{
    /** @var ScopeInterface[] */
    private $grantedScopes;

    /**
     * @param ScopeInterface[] $grantedScopes
     */
    public function __construct(array $grantedScopes = [])
    {
        $this->grantedScopes = $grantedScopes;
    }

    /** @return ScopeInterface[] */
    public function getGrantedScopes(): array
    {
        return $this->grantedScopes;
    }

    public function isAuthenticated(): bool
    {
        return false;
    }
}
