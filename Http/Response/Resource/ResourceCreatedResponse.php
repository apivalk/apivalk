<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response\Resource;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Resource\AbstractResource;

class ResourceCreatedResponse extends AbstractApivalkResponse
{
    /** @var AbstractResource */
    private $resource;

    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Just a dummy method to satisfy the interface. The documentation will be generated from the resource.
     */
    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        return new ApivalkResponseDocumentation();
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_201_CREATED;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'data' => $this->resource->toArray(AbstractResource::MODE_CREATE)
        ];
    }
}
