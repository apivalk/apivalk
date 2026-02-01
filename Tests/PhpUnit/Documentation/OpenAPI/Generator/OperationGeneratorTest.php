<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Generator\OperationGenerator;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class TestResponse extends AbstractApivalkResponse {
    public static function getDocumentation(): ApivalkResponseDocumentation {
        $doc = new ApivalkResponseDocumentation();
        $doc->setDescription('Success');
        return $doc;
    }
    public static function getStatusCode(): int { return 200; }
    public function toArray(): array { return []; }
}

class OperationGeneratorTest extends TestCase
{
    public function testOperationGenerator(): void
    {
        $generator = new OperationGenerator();
        
        $method = $this->createMock(GetMethod::class);
        $method->method('getName')->willReturn('GET');
        
        $route = $this->createMock(Route::class);
        $route->method('getMethod')->willReturn($method);
        $route->method('getDescription')->willReturn('Route desc');
        $route->method('getUrl')->willReturn('/test');
        $route->method('getTags')->willReturn([]);
        $route->method('getRouteAuthorization')->willReturn(null);
        
        $requestDoc = $this->createMock(ApivalkRequestDocumentation::class);
        $requestDoc->method('getPathProperties')->willReturn([]);
        $requestDoc->method('getQueryProperties')->willReturn([]);
        $requestDoc->method('getBodyProperties')->willReturn([]);
        
        $operation = $generator->generate($route, $requestDoc, [TestResponse::class]);
        
        $this->assertEquals('Route desc', $operation->getSummary());
        $this->assertCount(6, $operation->getResponses()); // 1 custom + 5 default
    }
}
