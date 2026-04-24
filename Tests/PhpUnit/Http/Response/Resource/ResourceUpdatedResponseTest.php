<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Resource;

use apivalk\apivalk\Http\Response\Resource\ResourceUpdatedResponse;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class ResourceUpdatedResponseTest extends TestCase
{
    public function testStatusCodeIs200(): void
    {
        self::assertSame(200, ResourceUpdatedResponse::getStatusCode());
    }

    public function testToArrayWrapsDataUnderKey(): void
    {
        $resource = new AnimalResource();
        $resource->name = 'Leo';

        $response = new ResourceUpdatedResponse($resource);

        self::assertArrayHasKey('data', $response->toArray());
    }
}
