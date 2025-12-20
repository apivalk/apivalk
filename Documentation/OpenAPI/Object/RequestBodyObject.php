<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class RequestBodyObject
 *
 * @see     https://swagger.io/specification/#request-body-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class RequestBodyObject implements ObjectInterface
{
    /** @var string|null */
    private $description;
    /** @var MediaTypeObject */
    private $content;
    /** @var bool */
    private $required;

    public function __construct(MediaTypeObject $content, ?string $description = null, bool $required = true)
    {
        $this->content = $content;
        $this->description = $description;
        $this->required = $required;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getContent(): MediaTypeObject
    {
        return $this->content;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'content' => array_filter($this->content->toArray()),
            'required' => $this->required
        ];
    }
}
