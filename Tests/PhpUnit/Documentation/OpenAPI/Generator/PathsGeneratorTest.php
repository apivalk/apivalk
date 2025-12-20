<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Generator\PathsGenerator;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Http\Method\GetMethod;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;

class PathsTestController extends AbstractApivalkController {
    public static function getRoute(): Route { return new Route('/test', new GetMethod()); }
    public static function getRequestClass(): string { return PathsTestRequest::class; }
    public static function getResponseClasses(): array { return []; }
    public function __invoke(ApivalkRequestInterface $request): \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
        return new class extends \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
            public static function getDocumentation(): \apivalk\apivalk\Documentation\ApivalkResponseDocumentation { return new \apivalk\apivalk\Documentation\ApivalkResponseDocumentation(); }
            public static function getStatusCode(): int { return 200; }
            public function toArray(): array { return []; }
        };
    }
}

class PathsTestRequest implements ApivalkRequestInterface {
    public static function getDocumentation(): ApivalkRequestDocumentation { return new ApivalkRequestDocumentation(); }
    public function populate(Route $route): void {}
    public function getMethod(): \apivalk\apivalk\Http\Method\MethodInterface { return new GetMethod(); }
    public function header(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createHeaderBag(); }
    public function query(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createQueryBag(self::getDocumentation()); }
    public function body(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createBodyBag(self::getDocumentation()); }
    public function path(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createPathBag(new Route('', new GetMethod()), self::getDocumentation()); }
    public function file(): \apivalk\apivalk\Http\Request\File\FileBag { return \apivalk\apivalk\Http\Request\File\FileBagFactory::create(); }
    public function getAuthIdentity(): ?\apivalk\apivalk\Security\AbstractAuthIdentity { return null; }
    public function setAuthIdentity(?\apivalk\apivalk\Security\AbstractAuthIdentity $authIdentity): void {}
}

class PathsGeneratorTest extends TestCase
{
    public function testPathsGenerator(): void
    {
        $generator = new PathsGenerator();
        
        $method = $this->createMock(GetMethod::class);
        $method->method('getName')->willReturn('GET');

        $route = $this->createMock(Route::class);
        $route->method('getMethod')->willReturn($method);
        $route->method('getUrl')->willReturn('/test');
        $route->method('getDescription')->willReturn('desc');
        $route->method('getTags')->willReturn([]);
        $route->method('getSecurityRequirements')->willReturn([]);

        $routes = [
            ['route' => $route, 'controllerClass' => PathsTestController::class]
        ];
        
        $paths = $generator->generate('/test', $routes);
        $this->assertArrayHasKey('/test', $paths->toArray());
    }
}
