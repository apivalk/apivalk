<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

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
    /** @var bool */
    private $hasPagination;

    public function __construct(
        string $type,
        bool $required = true,
        array $properties = [],
        bool $hasPagination = false
    ) {
        $this->type = $type;
        $this->required = $required;
        $this->properties = $properties;
        $this->hasPagination = $hasPagination;
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

    public function hasPagination(): bool
    {
        return $this->hasPagination;
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

        // ToDo: Improve at some point
        if ($this->hasPagination) {
            $properties['pagination'] = [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => [
                            'type' => 'integer',
                        ],
                        'total_pages' => [
                            'type' => 'integer',
                        ],
                        'page_size' => [
                            'type' => 'integer',
                        ]
                    ]
                ],
                'description' => 'Pagination information',
            ];
        }

        return [
            'type' => $this->type,
            'required' => $requiredPropertyNames,
            'properties' => $properties
        ];
    }
}
