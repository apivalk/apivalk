<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Resource;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationPaginationResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceListResponse;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class ResourceListResponseTest extends TestCase
{
    public function testStatusCodeIs200(): void
    {
        self::assertSame(200, ResourceListResponse::getStatusCode());
    }

    public function testToArrayContainsDataKey(): void
    {
        $resource = new AnimalResource();
        $resource->name = 'Leo';
        $resource->type = 'lion';

        $pagination = new PagePaginationPaginationResponse(1, 10, false);
        $response = new ResourceListResponse([$resource], $pagination);

        $array = $response->toArray();
        self::assertArrayHasKey('data', $array);
        self::assertIsArray($array['data']);
        self::assertCount(1, $array['data']);
    }

    public function testGetDocumentationReturnsEmptyDocumentation(): void
    {
        self::assertInstanceOf(ApivalkResponseDocumentation::class, ResourceListResponse::getDocumentation());
    }
}
