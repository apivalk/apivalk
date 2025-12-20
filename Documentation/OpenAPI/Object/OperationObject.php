<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Http\Method\DeleteMethod;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

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
    /** @var SecurityRequirementObject[] */
    private $security;
    /** @var MethodInterface */
    private $method;

    public function __construct(
        MethodInterface $method,
        array $tags = [],
        ?string $summary = null,
        ?string $description = null,
        ?string $operationId = null,
        array $parameters = [],
        ?RequestBodyObject $requestBody = null,
        array $responses = [],
        ?array $security = []
    ) {
        $this->method = $method;
        $this->tags = $tags;
        $this->summary = $summary;
        $this->description = $description;
        $this->operationId = $operationId;
        $this->parameters = $parameters;
        $this->requestBody = $requestBody;
        $this->responses = $responses;
        $this->security = $security;
    }

    public function getMethod(): MethodInterface
    {
        return $this->method;
    }

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

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getRequestBody(): ?RequestBodyObject
    {
        return $this->requestBody;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getSecurity(): array
    {
        return $this->security;
    }

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
        foreach ($this->security as $security) {
            $securities[] = $security->toArray();
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
        ];
    }
}
