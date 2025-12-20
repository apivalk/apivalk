<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class MediaTypeObject
 *
 * @see     https://swagger.io/specification/#media-type-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class MediaTypeObject implements ObjectInterface
{
    /** @var string */
    private $mediaType;
    /** @var SchemaObject */
    private $schema;

    public function __construct(SchemaObject $schema, string $mediaType = 'application/json')
    {
        $this->mediaType = $mediaType;
        $this->schema = $schema;
    }

    public function getSchema(): SchemaObject
    {
        return $this->schema;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function toArray(): array
    {
        return [
            $this->mediaType => [
                'schema' => array_filter($this->schema->toArray())
            ]
        ];
    }
}
