<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\AuthIdentity;

class GuestAuthIdentity extends AbstractAuthIdentity
{
    /** @var string[] */
    private $scopes;

    /** @param string[] $scopes */
    public function __construct(array $scopes = [])
    {
        $this->scopes = $scopes;
    }

    /** @return string[] */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function isAuthenticated(): bool
    {
        return false;
    }

    public function getPermissions(): array
    {
        return [];
    }
}
