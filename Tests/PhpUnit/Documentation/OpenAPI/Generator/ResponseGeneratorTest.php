<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Generator\ResponseGenerator;
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
    }
}
