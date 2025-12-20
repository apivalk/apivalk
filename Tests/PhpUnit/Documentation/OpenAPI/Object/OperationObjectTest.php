<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\OperationObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ParameterObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\RequestBodyObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ResponseObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Method\PostMethod;

class OperationObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $method = $this->createMock(PostMethod::class);
        $method->method('getName')->willReturn('POST');
        
        $tag = $this->createMock(TagObject::class);
        $tag->method('getName')->willReturn('User');
        
        $parameter = $this->createMock(ParameterObject::class);
        $parameter->method('toArray')->willReturn(['name' => 'id', 'in' => 'path']);
        
        $requestBody = $this->createMock(RequestBodyObject::class);
        $requestBody->method('toArray')->willReturn(['description' => 'Body']);
        
        $response = $this->createMock(ResponseObject::class);
        $response->method('toArray')->willReturn([200 => ['description' => 'Success']]);
        
        $security = $this->createMock(SecurityRequirementObject::class);
        $security->method('toArray')->willReturn(['BearerAuth' => []]);

        $operation = new OperationObject(
            $method,
            [$tag],
            'Summary',
            'Description',
            'postUser',
            [$parameter],
            $requestBody,
            [$response],
            [$security]
        );

        $result = $operation->toArray();

        $this->assertEquals(['User'], $result['tags']);
        $this->assertEquals('Summary', $result['summary']);
        $this->assertEquals('postUser', $result['operationId']);
        $this->assertCount(1, $result['parameters']);
        $this->assertArrayHasKey('requestBody', $result);
        $this->assertArrayHasKey(200, $result['responses']);
        $this->assertEquals([['BearerAuth' => []]], $result['security']);
        
        $this->assertSame($method, $operation->getMethod());
        $this->assertEquals([$tag], $operation->getTags());
        $this->assertEquals('Summary', $operation->getSummary());
        $this->assertEquals('Description', $operation->getDescription());
        $this->assertEquals('postUser', $operation->getOperationId());
        $this->assertEquals([$parameter], $operation->getParameters());
        $this->assertSame($requestBody, $operation->getRequestBody());
        $this->assertEquals([$response], $operation->getResponses());
        $this->assertEquals([$security], $operation->getSecurity());
    }

    public function testRequestBodyExcludedForGet(): void
    {
        $method = $this->createMock(GetMethod::class);
        $requestBody = $this->createMock(RequestBodyObject::class);

        $operation = new OperationObject(
            $method,
            [],
            null,
            null,
            null,
            [],
            $requestBody
        );

        $result = $operation->toArray();
        $this->assertNull($result['requestBody']);
    }
}
