<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\OpenAPI\Object\MediaTypeObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;

class MediaTypeGenerator
{
    public function generate(string $mediaType, SchemaObject $schemaObject): MediaTypeObject
    {
        return new MediaTypeObject(
            $schemaObject,
            $mediaType
        );
    }
}
