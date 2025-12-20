<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class ParameterObject
 *
 * @see     https://swagger.io/specification/#parameter-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
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
    /** @var SingleSchemaObject|null */
    private $singleSchemaObject;

    public function __construct(
        string $name,
        string $in,
        ?string $description = null,
        bool $required = true,
        ?SingleSchemaObject $singleSchemaObject = null
    ) {
        $this->name = $name;
        $this->in = $in;
        $this->description = $description;
        $this->required = $required;
        $this->singleSchemaObject = $singleSchemaObject;
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

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'in' => $this->in,
            'description' => $this->description,
            'required' => $this->required,
            'schema' => $this->singleSchemaObject instanceof SingleSchemaObject ? $this->singleSchemaObject->toArray()
                : null,
        ];
    }
}
