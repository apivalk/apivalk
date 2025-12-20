<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class ComponentsObject
 *
 * @see     https://swagger.io/specification/#components-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class ComponentsObject implements ObjectInterface
{
    /** @var array<string, SchemaObject> */
    private $schemas = [];
    /** @var array<string, ResponseObject> */
    private $responses = [];
    /** @var array<string, ParameterObject> */
    private $parameters = [];
    /** @var array<string, RequestBodyObject> */
    private $requestBodies = [];
    /** @var array<string, HeaderObject> */
    private $headers = [];
    /** @var array<string, SecuritySchemeObject> */
    private $securitySchemes = [];
    /** @var array<string, PathItemObject> */
    private $pathItems = [];

    public function getSchemas(): array
    {
        return $this->schemas;
    }

    public function setSchemas(array $schemas): void
    {
        $this->schemas = $schemas;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function setResponses(array $responses): void
    {
        $this->responses = $responses;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getRequestBodies(): array
    {
        return $this->requestBodies;
    }

    public function setRequestBodies(array $requestBodies): void
    {
        $this->requestBodies = $requestBodies;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function getSecuritySchemes(): array
    {
        return $this->securitySchemes;
    }

    public function setSecuritySchemes(array $securitySchemes): void
    {
        $this->securitySchemes = $securitySchemes;
    }

    public function getPathItems(): array
    {
        return $this->pathItems;
    }

    public function setPathItems(array $pathItems): void
    {
        $this->pathItems = $pathItems;
    }

    public function toArray(): array
    {
        $schemas = array_map(static function ($schema) {
            return array_filter($schema->toArray());
        }, $this->schemas);

        $responses = array_map(static function ($response) {
            return array_filter($response->toArray());
        }, $this->responses);

        $parameters = array_map(static function ($parameter) {
            return array_filter($parameter->toArray());
        }, $this->parameters);

        $requestBodies = array_map(static function ($requestBody) {
            return array_filter($requestBody->toArray());
        }, $this->requestBodies);

        $headers = array_map(static function ($header) {
            return array_filter($header->toArray());
        }, $this->headers);

        $securitySchemes = [];
        foreach ($this->securitySchemes as $securityScheme) {
            $securitySchemes[$securityScheme->getName()] = array_filter($securityScheme->toArray());
        }

        $pathItems = array_map(static function ($pathItem) {
            return array_filter($pathItem->toArray());
        }, $this->pathItems);

        return array_filter(
            [
                'schemas' => $schemas,
                'responses' => $responses,
                'parameters' => $parameters,
                'requestBodies' => $requestBodies,
                'headers' => $headers,
                'securitySchemes' => $securitySchemes,
                'pathItems' => $pathItems,
            ]
        );
    }
}
