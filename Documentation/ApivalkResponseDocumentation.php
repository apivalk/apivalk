<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

class ApivalkResponseDocumentation
{
    /** @var string|null */
    private $description;
    /** @var AbstractProperty[] */
    private $properties = [];
    /** @var bool */
    private $hasResponsePagination = false;

    public function addProperty(AbstractProperty $property): void
    {
        $this->properties[] = $property->init();
    }

    public function setHasResponsePagination(bool $hasResponsePagination): void
    {
        $this->hasResponsePagination = $hasResponsePagination;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function hasResponsePagination(): bool
    {
        return $this->hasResponsePagination;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
