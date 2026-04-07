<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\RequestBodyGenerator;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class RequestBodyGeneratorTest extends TestCase
{
    public function testRequestBodyGenerator(): void
    {
        $generator = new RequestBodyGenerator();
        $doc = $this->createMock(ApivalkRequestDocumentation::class);
        $route = $this->createMock(Route::class);
        $route->method('getDescription')->willReturn('Description');
        
        $requestBody = $generator->generate($doc, $route);
        
        $this->assertEquals('Description', $requestBody->getDescription());
    }
}
