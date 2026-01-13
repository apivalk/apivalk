<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security;

class Scope implements ScopeInterface
{
    /** @var string */
    private $name;
    /** @var string|null */
    private $description;

    public function __construct(string $name, ?string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
