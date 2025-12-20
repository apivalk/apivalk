<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\ResponseObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\MediaTypeObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\HeaderObject;

class ResponseObjectTest extends TestCase
{
    public function testResponseObjectToArray(): void
    {
        $mediaType = $this->createMock(MediaTypeObject::class);
        $mediaType->method('toArray')->willReturn([
            'application/json' => [
                'schema' => ['type' => 'object']
            ]
        ]);
        
        $header = $this->createMock(HeaderObject::class);
        
        $response = new ResponseObject(
            200,
            $mediaType,
            'OK',
            ['X-Test' => $header]
        );
        
        $result = $response->toArray();

        $this->assertArrayHasKey(200, $result);
        $this->assertEquals('OK', $result[200]['description']);
        $this->assertArrayHasKey('X-Test', $result[200]['headers']);
        $this->assertArrayHasKey('content', $result[200]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getDescription());
        $this->assertSame($mediaType, $response->getContent());
        $this->assertCount(1, $response->getHeaders());
    }

    public function testResponseObjectMinimal(): void
    {
        $response = new ResponseObject(204);
        
        $result = $response->toArray();

        $this->assertArrayHasKey(204, $result);
        $this->assertEquals('', $result[204]['description']);
    }
}
