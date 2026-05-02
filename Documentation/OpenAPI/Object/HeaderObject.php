<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class HeaderObject
 *
 * @see     https://swagger.io/specification/#header-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class HeaderObject implements ObjectInterface
{
    /** @var string|null */
    private $description;
    /** @var bool */
    private $required;
    /** @var array<string, mixed>|null */
    private $schema;

    /**
     * @param array<string, mixed>|null $schema
     */
    public function __construct(?string $description, bool $required = false, ?array $schema = null)
    {
        $this->description = $description;
        $this->required = $required;
        $this->schema = $schema;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSchema(): ?array
    {
        return $this->schema;
    }

    public function toArray(): array
    {
        $array = [
            'description' => $this->description,
            'required' => $this->required,
        ];

        if ($this->schema !== null) {
            $array['schema'] = $this->schema;
        }

        return $array;
    }
}
