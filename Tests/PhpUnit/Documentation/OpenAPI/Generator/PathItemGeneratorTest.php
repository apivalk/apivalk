<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\PathItemGenerator;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use PHPUnit\Framework\TestCase;

class PathItemTestController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/test', new GetMethod());
    }

    public static function getRequestClass(): string
    {
        return PathItemTestRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [];
    }

    public function __invoke(ApivalkRequestInterface $request): \apivalk\apivalk\Http\Response\AbstractApivalkResponse
    {
        return new class extends \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
            public static function getDocumentation(): \apivalk\apivalk\Documentation\ApivalkResponseDocumentation
            {
                return new \apivalk\apivalk\Documentation\ApivalkResponseDocumentation();
            }

            public static function getStatusCode(): int
            {
                return 200;
            }

            public function toArray(): array
            {
                return [];
            }
        };
    }
}

class PathItemTestRequest implements ApivalkRequestInterface
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }

    public function populate(Route $route): void
    {
    }

    public function getMethod(): \apivalk\apivalk\Http\Method\MethodInterface
    {
        return new GetMethod();
    }

    public function header(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag
    {
        return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createHeaderBag();
    }

    public function query(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag
    {
        return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createQueryBag(self::getDocumentation());
    }

    public function body(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag
    {
        return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createBodyBag(self::getDocumentation());
    }

    public function path(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag
    {
        return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createPathBag(
            new Route('', new GetMethod()),
            self::getDocumentation()
        );
    }

    public function file(): \apivalk\apivalk\Http\Request\File\FileBag
    {
        return \apivalk\apivalk\Http\Request\File\FileBagFactory::create();
    }

    public function getAuthIdentity(): \apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity
    {
        return new GuestAuthIdentity([]);
    }

    public function setAuthIdentity(\apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity $authIdentity): void
    {
    }

    public function getIp(): string
    {
        return '127.0.0.1';
    }

    public function getRateLimitResult(): ?RateLimitResult
    {
        return null;
    }

    public function setRateLimitResult(RateLimitResult $rateLimitResult): void
    {
    }
}

class PathItemGeneratorTest extends TestCase
{
    public function testPathItemGenerator(): void
    {
        $generator = new PathItemGenerator();

        $method = $this->createMock(GetMethod::class);
        $method->method('getName')->willReturn('GET');

        $route = $this->createMock(Route::class);
        $route->method('getMethod')->willReturn($method);
        $route->method('getUrl')->willReturn('/test');
        $route->method('getDescription')->willReturn('desc');
        $route->method('getTags')->willReturn([]);
        $route->method('getSecurityRequirements')->willReturn([]);

        $routes = [
            ['route' => $route, 'controllerClass' => PathItemTestController::class]
        ];

        $pathItem = $generator->generate($routes);
        $this->assertNotNull($pathItem->getGet());
    }
}
