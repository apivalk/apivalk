<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Http\Response\Pagination\CursorPaginationResponse;
use apivalk\apivalk\Http\Response\Pagination\OffsetPaginationResponse;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationResponse;
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
    private string $type;
    private bool $required;
    /** @var AbstractProperty[] */
    private array $properties;
    private ?Pagination $pagination;

    /** @var array<string, mixed>|null */
    private ?array $rawSchema = null;

    /**
     * A schema the generator assembles itself rather than deriving from properties, used
     * for the operator maps of the QUERY filter body.
     *
     * @param array<string, mixed> $schema
     */
    public static function raw(array $schema): self
    {
        $instance = new self('object', false);
        $instance->rawSchema = $schema;

        return $instance;
    }

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
        if ($this->rawSchema !== null) {
            return $this->rawSchema;
        }

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
                    $paginationProperties = PagePaginationResponse::getResponseDocumentationProperties();
                    break;
                case Pagination::TYPE_OFFSET:
                    $paginationProperties = OffsetPaginationResponse::getResponseDocumentationProperties();
                    break;
                case Pagination::TYPE_CURSOR:
                    $paginationProperties = CursorPaginationResponse::getResponseDocumentationProperties();
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

        $schema = ['type' => $this->type];

        // An empty PHP array serialises to `[]`, but both keywords must be objects in JSON
        // Schema, and an empty `required` carries no meaning either. Omit them instead.
        if ($requiredPropertyNames !== []) {
            $schema['required'] = $requiredPropertyNames;
        }

        if ($properties !== []) {
            $schema['properties'] = $properties;
        }

        return $schema;
    }
}
