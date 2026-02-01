<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\AuthIdentity;

class JwtAuthIdentity extends AbstractAuthIdentity
{
    /** @var string|null */
    private $username;
    /** @var string|null */
    private $email;
    /** @var string|null */
    private $sub;
    /** @var string[] */
    private $scopes;
    /** @var string[] */
    private $permissions;

    /**
     * @param string[] $scopes
     * @param string[] $permissions
     */
    public function __construct(
        ?string $username,
        ?string $email,
        ?string $sub,
        array $scopes,
        array $permissions
    ) {
        $this->scopes = $scopes;
        $this->permissions = $permissions;
        $this->username = $username;
        $this->email = $email;
        $this->sub = $sub;
    }

    /** @return string[] */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /** @return string[] */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function isAuthenticated(): bool
    {
        return true;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getSub(): ?string
    {
        return $this->sub;
    }
}
