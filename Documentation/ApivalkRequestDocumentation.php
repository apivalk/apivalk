<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\NumberProperty;

class ApivalkRequestDocumentation
{
    /** @var array<string, AbstractProperty> */
    private $bodyProperties = [];
    /** @var array<string, AbstractProperty> */
    private $queryProperties = [];
    /** @var array<string, AbstractProperty> */
    private $pathProperties = [];

    public function addBodyProperty(AbstractProperty $property): void
    {
        $this->bodyProperties[$property->getPropertyName()] = $property;
    }

    public function addQueryProperty(AbstractProperty $property): void
    {
        $this->queryProperties[$property->getPropertyName()] = $property;
    }

    public function addPathProperty(AbstractProperty $property): void
    {
        $this->pathProperties[$property->getPropertyName()] = $property;
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

    public function addPaginationQueryProperties(): void
    {
        $pageProperty = new NumberProperty('page', 'Page');
        $pageProperty->setIsRequired(false);
        $pageProperty->setFormat(NumberProperty::FORMAT_INT32);
        $pageProperty->setExample('1');

        $this->addQueryProperty($pageProperty);
    }
}
