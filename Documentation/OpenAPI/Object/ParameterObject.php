<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;

/**
 * Class ParameterObject
 *
 * @see https://swagger.io/specification/#parameter-object
 */
class ParameterObject implements ObjectInterface
{
    /** @var string */
    private $name;

    /** @var string */
    private $in;

    /** @var string|null */
    private $description;

    /** @var bool */
    private $required;

    /** @var AbstractProperty */
    private $property;

    /** @var string|null */
    private $style;

    /** @var bool|null */
    private $explode;

    /** @var array<string, mixed>|null */
    private $rawSchema;

    public function __construct(string $in, AbstractProperty $property)
    {
        $this->name        = $property->getPropertyName();
        $this->in          = $in;
        $this->description = $property->getPropertyDescription();
        $this->required    = $property->isRequired();
        $this->property    = $property;
        $this->style       = null;
        $this->explode     = null;
        $this->rawSchema   = null;
    }

    /**
     * Build a deepObject parameter that groups all filters under a single `filter` key.
     * Produces: ?filter[field]=value — each property becomes a named sub-field.
     *
     * @param AbstractProperty[] $properties
     */
    public static function forFilterGroup(array $properties): self
    {
        $placeholder = new StringProperty(
            'filter',
            'Filter results. Pass each field using bracket notation: ?filter[field]=value'
        );
        $placeholder->setIsRequired(false);

        $instance = new self('query', $placeholder);
        $instance->style   = 'deepObject';
        $instance->explode = true;

        $subProperties = [];
        foreach ($properties as $property) {
            $subProperties[$property->getPropertyName()] = $property->getDocumentationArray();
        }

        $instance->rawSchema = [
            'type'       => 'object',
            'properties' => $subProperties,
        ];

        return $instance;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIn(): string
    {
        return $this->in;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getProperty(): ?AbstractProperty
    {
        return $this->property;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function toArray(): array
    {
        $schema = $this->rawSchema !== null
            ? $this->rawSchema
            : $this->property->getDocumentationArray();

        return array_filter(
            [
                'name'        => $this->name,
                'in'          => $this->in,
                'description' => $this->description,
                'required'    => $this->required,
                'style'       => $this->style,
                'explode'     => $this->explode,
                'schema'      => $schema,
            ],
            static function ($value) {
                return $value !== null;
            }
        );
    }
}
