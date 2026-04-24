<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Resource;

use apivalk\apivalk\Http\Response\Resource\ResourceViewResponse;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class ResourceViewResponseTest extends TestCase
{
    public function testStatusCodeIs200(): void
    {
        self::assertSame(200, ResourceViewResponse::getStatusCode());
    }

    public function testToArrayWrapsDataUnderKey(): void
    {
        $resource = new AnimalResource();
        $resource->name = 'Leo';
        $resource->type = 'lion';

        $response = new ResourceViewResponse($resource);

        $array = $response->toArray();
        self::assertArrayHasKey('data', $array);
        self::assertIsArray($array['data']);
    }
}
