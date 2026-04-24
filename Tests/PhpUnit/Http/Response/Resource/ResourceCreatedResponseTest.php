<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Resource;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\Resource\ResourceCreatedResponse;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class ResourceCreatedResponseTest extends TestCase
{
    public function testStatusCodeIs201(): void
    {
        $resource = $this->makeResource();
        $response = new ResourceCreatedResponse($resource);

        self::assertSame(201, $response::getStatusCode());
    }

    public function testToArrayWrapsDataUnderKey(): void
    {
        $resource = $this->makeResource();
        $response = new ResourceCreatedResponse($resource);

        $array = $response->toArray();

        self::assertArrayHasKey('data', $array);
    }

    public function testGetDocumentationReturnsEmptyDocumentation(): void
    {
        $doc = ResourceCreatedResponse::getDocumentation();

        self::assertInstanceOf(ApivalkResponseDocumentation::class, $doc);
    }

    private function makeResource(): AbstractResource
    {
        $resource = new AnimalResource();
        $resource->animal_uuid = 'abc-123';
        $resource->name = 'Leo';
        $resource->type = 'lion';

        return $resource;
    }
}
