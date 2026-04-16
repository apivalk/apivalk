<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Resource;

use apivalk\apivalk\Http\Response\Resource\ResourceDeletedResponse;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class ResourceDeletedResponseTest extends TestCase
{
    public function testStatusCodeIs200(): void
    {
        self::assertSame(200, ResourceDeletedResponse::getStatusCode());
    }

    public function testToArrayWrapsDataUnderKey(): void
    {
        $resource = new AnimalResource();
        $resource->name = 'Leo';

        $response = new ResourceDeletedResponse($resource);

        self::assertArrayHasKey('data', $response->toArray());
    }
}
