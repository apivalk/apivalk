<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\FileProperty;

class ApivalkRequestDocumentation
{
    /** @var array<string, AbstractProperty> */
    private $bodyProperties = [];
    /** @var array<string, AbstractProperty> */
    private $queryProperties = [];
    /** @var array<string, AbstractProperty> */
    private $pathProperties = [];
    /** @var array<string, FileProperty> */
    private $fileProperties = [];
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

    /** Declares an uploaded file of a multipart/form-data request. */
    public function addFileProperty(FileProperty $property): void
    {
        $this->fileProperties[$property->getPropertyName()] = $property;
    }

    /** @return array<string, FileProperty> */
    public function getFileProperties(): array
    {
        return $this->fileProperties;
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
