<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\Authenticator;

use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;

interface AuthenticatorInterface
{
    public function authenticate(string $token): ?AbstractAuthIdentity;
}
