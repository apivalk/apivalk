<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Http\Response\Pagination\CursorPaginationPaginationResponse;
use apivalk\apivalk\Http\Response\Pagination\OffsetPaginationPaginationResponse;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationPaginationResponse;
use apivalk\apivalk\Router\Route\Pagination\Pagination;

/**
 * Class SchemaObject
 *
 * @see     https://swagger.io/specification/#schema-object - Based on Simple Model
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class SchemaObject implements ObjectInterface
{
    /** @var string */
    private $type;
    /** @var bool */
    private $required;
    /** @var AbstractProperty[] */
    private $properties;
    /** @var Pagination|null */
    private $pagination;

    public function __construct(
        string $type,
        bool $required = true,
        array $properties = [],
        ?Pagination $pagination = null
    ) {
        $this->type = $type;
        $this->required = $required;
        $this->properties = $properties;
        $this->pagination = $pagination;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getPagination(): ?Pagination
    {
        return $this->pagination;
    }

    public function toArray(): array
    {
        $requiredPropertyNames = [];
        $properties = [];
        foreach ($this->properties as $property) {
            $properties[$property->getPropertyName()] = $property->getDocumentationArray();

            if ($property->isRequired()) {
                $requiredPropertyNames[] = $property->getPropertyName();
            }
        }

        if ($this->getPagination() !== null) {
            $paginationProperties = [];

            switch ($this->pagination->getType()) {
                case Pagination::TYPE_PAGE:
                    $paginationProperties = PagePaginationPaginationResponse::getResponseDocumentationProperties();
                    break;
                case Pagination::TYPE_OFFSET:
                    $paginationProperties = OffsetPaginationPaginationResponse::getResponseDocumentationProperties();
                    break;
                case Pagination::TYPE_CURSOR:
                    $paginationProperties = CursorPaginationPaginationResponse::getResponseDocumentationProperties();
                    break;
            }

            $paginationPropertiesArray = [];

            foreach ($paginationProperties as $paginationProperty) {
                $paginationPropertiesArray[$paginationProperty->getPropertyName()] =
                    $paginationProperty->getDocumentationArray();
            }

            $properties['pagination'] = [
                'type' => 'object',
                'properties' => $paginationPropertiesArray,
                'description' => 'Pagination',
            ];
        }

        return [
            'type' => $this->type,
            'required' => $requiredPropertyNames,
            'properties' => $properties
        ];
    }
}
