<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security;

class RouteAuthorization
{
    private string $securitySchemeName;
    /** @var string[] */
    private array $requiredScopes;
    /** @var string[] */
    private array $requiredPermissions;

    /**
     * @param string        $securitySchemeName
     * @param string[]|null $scopes
     * @param string[]|null $permissions
     */
    public function __construct(string $securitySchemeName, ?array $scopes = null, ?array $permissions = null)
    {
        $this->securitySchemeName = $securitySchemeName;
        $this->requiredScopes = $scopes ?? [];
        $this->requiredPermissions = $permissions ?? [];
    }

    public function getSecuritySchemeName(): string
    {
        return $this->securitySchemeName;
    }

    /**
     * @return string[]
     */
    public function getRequiredScopes(): array
    {
        return $this->requiredScopes;
    }

    /**
     * @return string[]
     */
    public function getRequiredPermissions(): array
    {
        return $this->requiredPermissions;
    }
}
