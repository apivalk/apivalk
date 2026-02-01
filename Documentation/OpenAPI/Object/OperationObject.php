<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Http\Method\DeleteMethod;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Security\RouteAuthorization;

/**
 * Class OperationObject
 *
 * @see     https://swagger.io/specification/#operation-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class OperationObject implements ObjectInterface
{
    /** @var TagObject[] */
    private $tags;
    /** @var string|null */
    private $summary;
    /** @var string|null */
    private $description;
    /** @var string|null */
    private $operationId;
    /** @var ParameterObject[] */
    private $parameters;
    /** @var RequestBodyObject|null */
    private $requestBody;
    /** @var ResponseObject[] */
    private $responses;
    /** @var RouteAuthorization */
    private $routeAuthorization;
    /** @var MethodInterface */
    private $method;

    /**
     * @param MethodInterface $method
     * @param TagObject[] $tags
     * @param string|null $summary
     * @param string|null $description
     * @param string|null $operationId
     * @param ParameterObject[] $parameters
     * @param RequestBodyObject|null $requestBody
     * @param ResponseObject[] $responses
     * @param RouteAuthorization|null $routeAuthorization
     */
    public function __construct(
        MethodInterface $method,
        array $tags = [],
        ?string $summary = null,
        ?string $description = null,
        ?string $operationId = null,
        array $parameters = [],
        ?RequestBodyObject $requestBody = null,
        array $responses = [],
        ?RouteAuthorization $routeAuthorization
    ) {
        $this->method = $method;
        $this->tags = $tags;
        $this->summary = $summary;
        $this->description = $description;
        $this->operationId = $operationId;
        $this->parameters = $parameters;
        $this->requestBody = $requestBody;
        $this->responses = $responses;
        $this->routeAuthorization = $routeAuthorization;
    }

    public function getMethod(): MethodInterface
    {
        return $this->method;
    }

    /**
     * @return TagObject[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getOperationId(): ?string
    {
        return $this->operationId;
    }

    /**
     * @return ParameterObject[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getRequestBody(): ?RequestBodyObject
    {
        return $this->requestBody;
    }

    /**
     * @return ResponseObject[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getRouteAuthorization(): ?RouteAuthorization
    {
        return $this->routeAuthorization;
    }

    /**
     * @return array{
     *     tags: string[],
     *     summary: string|null,
     *     description: string|null,
     *     operationId: string|null,
     *     parameters: array<int, mixed>,
     *     requestBody: array<string, mixed>|null,
     *     responses: array<string, mixed>,
     *     security: array<int, array<string, string[]>>,
     *     x-permissions: string[],
     *     x-scopes: string[]
     * }
     */
    public function toArray(): array
    {
        $parameters = [];
        foreach ($this->parameters as $parameter) {
            $parameters[] = array_filter($parameter->toArray());
        }

        $responses = [];
        /** @var AbstractApivalkResponse $response */
        foreach ($this->responses as $response) {
            $responses += array_filter($response->toArray());
        }

        $securities = [];
        $permissions = [];
        $scopes = [];

        if ($this->routeAuthorization !== null) {
            $securities[] = (new SecurityRequirementObject(
                $this->routeAuthorization->getSecuritySchemeName(),
                $this->routeAuthorization->getRequiredScopes()
            ))->toArray();

            $permissions = $this->routeAuthorization->getRequiredPermissions();
            $scopes = $this->routeAuthorization->getRequiredScopes();
        }

        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = $tag->getName();
        }

        return [
            'tags' => $tags,
            'summary' => $this->summary,
            'description' => $this->description,
            'operationId' => $this->operationId,
            'parameters' => $parameters,
            'requestBody' =>
                $this->requestBody instanceof RequestBodyObject
                && !$this->method instanceof GetMethod
                && !$this->method instanceof DeleteMethod ? array_filter(
                    $this->requestBody->toArray()
                ) : null,
            'responses' => $responses,
            'security' => $securities,
            'x-permissions' => $permissions,
            'x-scopes' => $scopes,
        ];
    }
}
