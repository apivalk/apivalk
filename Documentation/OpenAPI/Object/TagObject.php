<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class TagObject
 *
 * @see     https://swagger.io/specification/#tag-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class TagObject implements ObjectInterface
{
    /** @var string */
    private $name;
    /** @var string|null */
    private $description;

    public function __construct(string $name, ?string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description
        ];
    }
}
