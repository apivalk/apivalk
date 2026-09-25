<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\AuthIdentity;

abstract class AbstractAuthIdentity
{
    /** @var string[] */
    protected array $aud = [];

    /** @return string[] */
    abstract public function getScopes(): array;

    /** @return string[] */
    abstract public function getPermissions(): array;

    abstract public function isAuthenticated(): bool;

    /** @return string[] */
    public function getAud(): array
    {
        return $this->aud;
    }

    public function isScopeGranted(string $scope): bool
    {
        return \in_array($scope, $this->getScopes(), true);
    }

    public function isPermissionGranted(string $permission): bool
    {
        return \in_array($permission, $this->getPermissions(), true);
    }

    /**
     * A token is accepted when it carries at least one of the audiences the scheme lists.
     *
     * @param string[] $audiences
     */
    public function isAnyAudGranted(array $audiences): bool
    {
        return \array_intersect($audiences, $this->getAud()) !== [];
    }
}
