<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class SingleSchemaObject used for operation parameters
 *
 * @see     https://swagger.io/specification/#schema-object - Based on Simple Model
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class SingleSchemaObject implements ObjectInterface
{
    /** @var string */
    private $propertyName;
    /** @var string */
    private $type;
    /** @var bool */
    private $required;

    public function __construct(string $propertyName, string $type, bool $required = true)
    {
        $this->propertyName = $propertyName;
        $this->type = $type;
        $this->required = $required;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function toArray(): array
    {
        $requiredPropertyNames = [];
        if ($this->required) {
            $requiredPropertyNames[] = $this->propertyName;
        }

        return array_filter([
            'type' => $this->type,
            'required' => $requiredPropertyNames,
        ]);
    }
}
