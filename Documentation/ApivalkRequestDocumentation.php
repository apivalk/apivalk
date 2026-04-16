<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

class ApivalkRequestDocumentation
{
    /** @var array<string, AbstractProperty> */
    private $bodyProperties = [];
    /** @var array<string, AbstractProperty> */
    private $queryProperties = [];
    /** @var array<string, AbstractProperty> */
    private $pathProperties = [];
    /** @var string[] */
    private $availableSortFields = [];

    public function addBodyProperty(AbstractProperty $property): void
    {
        $this->bodyProperties[$property->getPropertyName()] = $property->init();
    }

    public function addQueryProperty(AbstractProperty $property): void
    {
        $this->queryProperties[$property->getPropertyName()] = $property->init();
    }

    public function addPathProperty(AbstractProperty $property): void
    {
        $this->pathProperties[$property->getPropertyName()] = $property->init();
    }

    public function getBodyProperties(): array
    {
        return $this->bodyProperties;
    }

    public function getQueryProperties(): array
    {
        return $this->queryProperties;
    }

    public function getPathProperties(): array
    {
        return $this->pathProperties;
    }

    public function addAvailableSortField(string $field): void
    {
        $this->availableSortFields[] = $field;
    }

    /** @return string[] */
    public function getAvailableSortFields(): array
    {
        return $this->availableSortFields;
    }
}
