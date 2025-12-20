<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security;

abstract class AbstractAuthIdentity
{
    /** @return string[] */
    abstract public function getGrantedScopes(): array;
}
