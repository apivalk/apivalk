<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Object\RequestBodyObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SchemaObject;
use apivalk\apivalk\Router\Route\Route;

class RequestBodyGenerator
{
    /** @var string */
    public const MEDIA_TYPE_JSON = 'application/json';
    /** @var string */
    public const MEDIA_TYPE_MULTIPART = 'multipart/form-data';

    public function generate(ApivalkRequestDocumentation $requestDocumentation, Route $route): RequestBodyObject
    {
        $mediaTypeGenerator = new MediaTypeGenerator();

        $fileProperties = $requestDocumentation->getFileProperties();

        // Uploads can only be transferred as multipart/form-data, so declared files decide the media type.
        $mediaType = \count($fileProperties) > 0 ? self::MEDIA_TYPE_MULTIPART : self::MEDIA_TYPE_JSON;

        $schema = new SchemaObject(
            'object',
            true,
            array_merge($requestDocumentation->getBodyProperties(), $fileProperties)
        );

        return new RequestBodyObject(
            $mediaTypeGenerator->generate($mediaType, $schema),
            $route->getDescription(),
            true
        );
    }
}
