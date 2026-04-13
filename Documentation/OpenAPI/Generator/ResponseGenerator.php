<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Object\HeaderObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ResponseObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;
use apivalk\apivalk\Router\Route\Route;

class ResponseGenerator
{
    /**
     * @param int                            $statusCode
     * @param ApivalkResponseDocumentation   $responseDocumentation
     * @param Route|null                     $route
     * @param array<string, HeaderObject>    $headers
     *
     * @return ResponseObject
     */
    public function generate(
        int $statusCode,
        ApivalkResponseDocumentation $responseDocumentation,
        ?Route $route = null,
        array $headers = []
    ): ResponseObject {
        $mediaTypeGenerator = new MediaTypeGenerator();

        $schema = new SchemaObject(
            'object',
            true,
            $responseDocumentation->getProperties(),
            $route instanceof Route ? $route->getPagination() : null
        );

        return new ResponseObject(
            $statusCode,
            $mediaTypeGenerator->generate('application/json', $schema),
            $responseDocumentation->getDescription(),
            $headers
        );
    }
}
