<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security;

class RouteAuthorization
{
    /** @var string */
    private $securitySchemeName;
    /** @var string[] */
    private $requiredScopes;
    /** @var string[] */
    private $requiredPermissions;

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
