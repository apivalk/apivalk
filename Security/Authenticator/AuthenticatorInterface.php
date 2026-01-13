<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\Authenticator;

use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;

interface AuthenticatorInterface
{
    /**
     * @param string $token
     * @return AbstractAuthIdentity|null Returns null if authentication fails
     */
    public function authenticate(string $token): ?AbstractAuthIdentity;
}
