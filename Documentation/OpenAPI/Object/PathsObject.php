<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class PathsObject
 *
 * @see     https://swagger.io/specification/#paths-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class PathsObject implements ObjectInterface
{
    /** @var string */
    private $url;
    /** @var PathItemObject */
    private $pathItem;

    public function __construct(string $url, PathItemObject $pathItem)
    {
        $this->url = $url;
        $this->pathItem = $pathItem;
    }

    public function getPathItem(): PathItemObject
    {
        return $this->pathItem;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function toArray(): array
    {
        return [
            $this->url => array_filter($this->pathItem->toArray())
        ];
    }
}
