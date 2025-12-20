<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\MediaTypeObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\RequestBodyObject;

class RequestBodyObjectTest extends TestCase
{
    public function testRequestBodyObjectToArray(): void
    {
        $mediaType = $this->createMock(MediaTypeObject::class);
        $mediaType->method('toArray')->willReturn([
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                ]
            ]
        ]);

        $requestBody = new RequestBodyObject($mediaType, 'Body description', true);
        
        $expected = [
            'description' => 'Body description',
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                    ]
                ]
            ],
            'required' => true
        ];

        $this->assertEquals($expected, $requestBody->toArray());
        $this->assertEquals('Body description', $requestBody->getDescription());
        $this->assertSame($mediaType, $requestBody->getContent());
        $this->assertTrue($requestBody->isRequired());
    }
}
