<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Object\ResponseObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;
use apivalk\apivalk\Router\Route\Route;

class ResponseGenerator
{
    public function generate(
        int $statusCode,
        ApivalkResponseDocumentation $responseDocumentation,
        ?Route $route = null
    ): ResponseObject {
        $mediaTypeGenerator = new MediaTypeGenerator();

        $schema = new SchemaObject(
            'object',
            true,
            $responseDocumentation->getProperties(),
            $route !== null ? $route->getPagination() : null
        );

        return new ResponseObject(
            $statusCode,
            $mediaTypeGenerator->generate('application/json', $schema),
            $responseDocumentation->getDescription(),
            []
        );
    }
}
