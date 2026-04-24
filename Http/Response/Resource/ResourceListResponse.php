<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response\Resource;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Pagination\PaginationResponseInterface;
use apivalk\apivalk\Resource\AbstractResource;

class ResourceListResponse extends AbstractApivalkResponse
{
    /** @var AbstractResource[] */
    private $resources;

    /**
     * @param AbstractResource[] $resources
     */
    public function __construct(array $resources, PaginationResponseInterface $paginationResponse)
    {
        $this->resources = $resources;

        $this->setPaginationResponse($paginationResponse);
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
        return self::HTTP_200_OK;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->resources as $resource) {
            $items[] = $resource->toArray(AbstractResource::MODE_LIST);
        }

        return [
            'data' => $items,
        ];
    }
}
