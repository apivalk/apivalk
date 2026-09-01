<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

class ApivalkResponseDocumentation
{
    private ?string $description = null;
    /** @var AbstractProperty[] */
    private array $properties = [];

    public function addProperty(AbstractProperty $property): void
    {
        $this->properties[] = $property->init();
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
