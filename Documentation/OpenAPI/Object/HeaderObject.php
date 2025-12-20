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

    public function __construct(?string $description, bool $required = false)
    {
        $this->description = $description;
        $this->required = $required;
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
            'description' => $this->description,
            'required' => $this->required
        ];
    }
}
