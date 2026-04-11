<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Object\ResponseObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;

class ResponseGenerator
{
    public function generate(int $statusCode, ApivalkResponseDocumentation $responseDocumentation): ResponseObject
    {
        $mediaTypeGenerator = new MediaTypeGenerator();

        $schema = new SchemaObject(
            'object',
            true,
            $responseDocumentation->getProperties(),
            $responseDocumentation->hasResponsePagination()
        );

        return new ResponseObject(
            $statusCode,
            $mediaTypeGenerator->generate('application/json', $schema),
            $responseDocumentation->getDescription(),
            []
        );
    }
}
