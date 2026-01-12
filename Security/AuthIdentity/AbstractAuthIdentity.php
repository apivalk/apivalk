<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\AuthIdentity;

use apivalk\apivalk\Security\ScopeInterface;

abstract class AbstractAuthIdentity
{
    /** @return ScopeInterface[] */
    abstract public function getGrantedScopes(): array;

    abstract public function isAuthenticated(): bool;

    public function isScopeGranted(ScopeInterface $scope): bool
    {
        return \in_array($scope, $this->getGrantedScopes(), true);
    }
}
