<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security;

interface ScopeInterface
{
    public function getName(): string;

    public function getDescription(): ?string;
}
