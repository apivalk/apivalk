<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Generator\ResponseGenerator;
use apivalk\apivalk\Documentation\OpenAPI\Object\HeaderObject;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;

class ResponseGeneratorTest extends TestCase
{
    public function testResponseGenerator(): void
    {
        $generator = new ResponseGenerator();
        $doc = $this->createMock(ApivalkResponseDocumentation::class);
        $doc->method('getDescription')->willReturn('Response desc');
        $doc->method('getProperties')->willReturn([]);
        $doc->method('hasResponsePagination')->willReturn(false);

        $response = $generator->generate(200, $doc);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Response desc', $response->getDescription());
        $this->assertEmpty($response->getHeaders());
    }

    public function testResponseGeneratorWithHeaders(): void
    {
        $generator = new ResponseGenerator();
        $doc = $this->createMock(ApivalkResponseDocumentation::class);
        $doc->method('getDescription')->willReturn('Response with headers');
        $doc->method('getProperties')->willReturn([]);
        $doc->method('hasResponsePagination')->willReturn(false);

        $headers = [
            'Content-Language' => new HeaderObject('The locale of the response content.'),
            'X-RateLimit-Limit' => new HeaderObject('Max requests allowed.'),
        ];

        $response = $generator->generate(200, $doc, null, $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(2, $response->getHeaders());
        $this->assertArrayHasKey('Content-Language', $response->getHeaders());
        $this->assertArrayHasKey('X-RateLimit-Limit', $response->getHeaders());
    }
}
