<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

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

    public function __construct(string $in, AbstractProperty $property)
    {
        $this->name = $property->getPropertyName();
        $this->in = $in;
        $this->description = $property->getPropertyDescription();
        $this->required = $property->isRequired();
        $this->property = $property;
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

    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'in' => $this->in,
                'description' => $this->description,
                'required' => $this->required,
                'schema' => $this->property->getDocumentationArray(),
            ],
            static function ($value) {
                return $value !== null;
            }
        );
    }
}
