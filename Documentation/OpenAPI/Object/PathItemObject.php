<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class PathItemObject
 *
 * @see     https://swagger.io/specification/#path-item-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class PathItemObject implements ObjectInterface
{
    private ?string $summary;
    private ?string $description;
    private ?OperationObject $get;
    private ?OperationObject $put;
    private ?OperationObject $post;
    private ?OperationObject $delete;
    private ?OperationObject $options;
    private ?OperationObject $head;
    private ?OperationObject $patch;
    private ?OperationObject $trace;
    /** @var ParameterObject[] */
    private array $parameters;

    public function __construct(
        ?string $summary = null,
        ?string $description = null,
        ?OperationObject $get = null,
        ?OperationObject $put = null,
        ?OperationObject $post = null,
        ?OperationObject $delete = null,
        ?OperationObject $options = null,
        ?OperationObject $head = null,
        ?OperationObject $patch = null,
        ?OperationObject $trace = null,
        array $parameters = []
    ) {
        $this->summary = $summary;
        $this->description = $description;
        $this->get = $get;
        $this->put = $put;
        $this->post = $post;
        $this->delete = $delete;
        $this->options = $options;
        $this->head = $head;
        $this->patch = $patch;
        $this->trace = $trace;
        $this->parameters = $parameters;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getGet(): ?OperationObject
    {
        return $this->get;
    }

    public function getPut(): ?OperationObject
    {
        return $this->put;
    }

    public function getPost(): ?OperationObject
    {
        return $this->post;
    }

    public function getDelete(): ?OperationObject
    {
        return $this->delete;
    }

    public function getOptions(): ?OperationObject
    {
        return $this->options;
    }

    public function getHead(): ?OperationObject
    {
        return $this->head;
    }

    public function getPatch(): ?OperationObject
    {
        return $this->patch;
    }

    public function getTrace(): ?OperationObject
    {
        return $this->trace;
    }

    /**
     * @return ParameterObject[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function toArray(): array
    {
        $parameters = [];
        foreach ($this->parameters as $parameter) {
            $parameters[] = array_filter($parameter->toArray());
        }

        return [
            'description' => $this->description,
            'summary' => $this->summary,
            'get' => $this->get instanceof OperationObject ? array_filter($this->get->toArray()) : null,
            'put' => $this->put instanceof OperationObject ? array_filter($this->put->toArray()) : null,
            'post' => $this->post instanceof OperationObject ? array_filter($this->post->toArray()) : null,
            'delete' => $this->delete instanceof OperationObject ? array_filter($this->delete->toArray()) : null,
            'options' => $this->options instanceof OperationObject ? array_filter($this->options->toArray()) : null,
            'head' => $this->head instanceof OperationObject ? array_filter($this->head->toArray()) : null,
            'patch' => $this->patch instanceof OperationObject ? array_filter($this->patch->toArray()) : null,
            'trace' => $this->trace instanceof OperationObject ? array_filter($this->trace->toArray()) : null,
            'parameters' => $parameters,
        ];
    }
}
