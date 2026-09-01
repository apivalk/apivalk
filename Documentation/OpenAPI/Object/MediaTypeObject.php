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
    private string $mediaType;
    private SchemaObject $schema;

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
                // Only null is dropped: a plain array_filter would also swallow
                // `additionalProperties: false` and any `0` default.
                'schema' => array_filter(
                    $this->schema->toArray(),
                    static fn($value) => $value !== null
                ),
            ]
        ];
    }
}
