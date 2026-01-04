<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\OpenAPIGenerator;
use apivalk\apivalk\Apivalk;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ServerObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ComponentsObject;

class OpenAPIGeneratorTest extends TestCase
{
    public function testGenerateJson(): void
    {
        $route = new Route('/test', new GetMethod());
        
        if (!class_exists('TestControllerForOpenAPI')) {
            eval('
                class TestRequestForOpenAPI extends apivalk\apivalk\Http\Request\AbstractApivalkRequest {
                    public static function getDocumentation(): apivalk\apivalk\Documentation\ApivalkRequestDocumentation {
                        return new apivalk\apivalk\Documentation\ApivalkRequestDocumentation();
                    }
                }

                class TestControllerForOpenAPI extends apivalk\apivalk\Http\Controller\AbstractApivalkController {
                public function __invoke(\apivalk\apivalk\Http\Request\ApivalkRequestInterface $request): \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
                    $response = new class extends \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
                        public static function getDocumentation(): \apivalk\apivalk\Documentation\ApivalkResponseDocumentation { return new \apivalk\apivalk\Documentation\ApivalkResponseDocumentation(); }
                        public static function getStatusCode(): int { return 200; }
                        public function toArray(): array { return []; }
                    };
                    return $response;
                }
                public static function getRoute(): \apivalk\apivalk\Router\Route { return new \apivalk\apivalk\Router\Route("/test", new \apivalk\apivalk\Http\Method\GetMethod()); }
                public static function getRequestClass(): string { return "TestRequestForOpenAPI"; }
                public static function getResponseClasses(): array { return []; }
            }');
        }
        $controllerClass = 'TestControllerForOpenAPI';

        $router = $this->createMock(AbstractRouter::class);
        $router->method('getRoutes')->willReturn([
            ['route' => $route, 'controllerClass' => $controllerClass]
        ]);

        $apivalk = $this->createMock(Apivalk::class);
        $apivalk->method('getRouter')->willReturn($router);

        $info = new InfoObject('Title', '1.0.0');
        $server = new ServerObject('http://localhost');
        $components = new ComponentsObject();

        $generator = new OpenAPIGenerator($apivalk, $info, [$server], $components);
        
        $json = $generator->generate();
        $this->assertInternalType('string', $json);
        
        $data = json_decode($json, true);
        $this->assertEquals('3.1.1', $data['openapi']);
        $this->assertEquals('Title', $data['info']['title']);
        $this->assertEquals('http://localhost', $data['servers'][0]['url']);
        $this->assertArrayHasKey('/test', $data['paths']);
    }

    public function testGenerateUnsupportedFormat(): void
    {
        $apivalk = $this->createMock(Apivalk::class);
        $generator = new OpenAPIGenerator($apivalk);

        $this->expectException(\InvalidArgumentException::class);
        $generator->generate('yaml');
    }
}
