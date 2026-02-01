<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\AuthIdentity;

abstract class AbstractAuthIdentity
{
    /** @return string[] */
    abstract public function getScopes(): array;

    /** @return string[] */
    abstract public function getPermissions(): array;

    abstract public function isAuthenticated(): bool;

    public function isScopeGranted(string $scope): bool
    {
        return \in_array($scope, $this->getScopes(), true);
    }

    public function isPermissionGranted(string $permission): bool
    {
        return \in_array($permission, $this->getPermissions(), true);
    }
}
