<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Object\RequestBodyObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;
use apivalk\apivalk\Router\Route;

class RequestBodyGenerator
{
    public function generate(ApivalkRequestDocumentation $requestDocumentation, Route $route): RequestBodyObject
    {
        $mediaTypeGenerator = new MediaTypeGenerator();

        $schema = new SchemaObject('object', true, $requestDocumentation->getBodyProperties());

        return new RequestBodyObject(
            $mediaTypeGenerator->generate('application/json', $schema),
            $route->getDescription(),
            true
        );
    }
}
