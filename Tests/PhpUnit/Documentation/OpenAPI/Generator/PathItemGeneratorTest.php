<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\PathItemGenerator;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\CreateAnimalController;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\DeleteAnimalController;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\ListAnimalsController;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\UpdateAnimalController;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\ViewAnimalController;
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

    public function populate(Route $route, ApivalkRequestDocumentation $documentation): void
    {
    }

    public function getRuntimeDocumentation(): ApivalkRequestDocumentation
    {
        return self::getDocumentation();
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
        return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createQueryBag(
            new Route('', new GetMethod()),
            self::getDocumentation()->getQueryProperties()
        );
    }

    public function body(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag
    {
        return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createBodyBag(
            self::getDocumentation()->getBodyProperties()
        );
    }

    public function path(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag
    {
        return \apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory::createPathBag(
            new Route('', new GetMethod()),
            self::getDocumentation()->getPathProperties()
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

    public function getLocale(): Locale
    {
        return Locale::en();
    }

    public function setLocale(Locale $locale): void
    {
    }

    public function sorting(): SortBag
    {
        return new SortBag();
    }

    public function filtering(): \apivalk\apivalk\Router\Route\Filter\FilterBag
    {
        return new \apivalk\apivalk\Router\Route\Filter\FilterBag();
    }

    public function paginator()
    {
        return new Pagination('page');
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
        $route->method('getRouteAuthorization')->willReturn(null);
        $route->method('getFilters')->willReturn([]);

        $routes = [
            ['route' => $route, 'controllerClass' => PathItemTestController::class]
        ];

        $pathItem = $generator->generate($routes);
        $this->assertNotNull($pathItem->getGet());
    }

    public function testListResourceControllerProducesGetOperation(): void
    {
        $generator = new PathItemGenerator();

        $route = ListAnimalsController::getRoute();

        $pathItem = $generator->generate([
            ['route' => $route, 'controllerClass' => ListAnimalsController::class],
        ]);

        $this->assertNotNull($pathItem->getGet());
        $this->assertNull($pathItem->getPost());
        $this->assertNull($pathItem->getPatch());
        $this->assertNull($pathItem->getDelete());
    }

    public function testCreateResourceControllerProducesPostOperationWithBody(): void
    {
        $generator = new PathItemGenerator();

        $route = CreateAnimalController::getRoute();

        $pathItem = $generator->generate([
            ['route' => $route, 'controllerClass' => CreateAnimalController::class],
        ]);

        $this->assertNotNull($pathItem->getPost());

        $requestBody = $pathItem->getPost()->getRequestBody();
        $this->assertNotNull($requestBody, 'Create operation must include request body documentation.');
    }

    public function testViewResourceControllerProducesGetOperationWithIdentifierPathParameter(): void
    {
        $generator = new PathItemGenerator();

        $route = ViewAnimalController::getRoute();

        $pathItem = $generator->generate([
            ['route' => $route, 'controllerClass' => ViewAnimalController::class],
        ]);

        $this->assertNotNull($pathItem->getGet());

        $hasIdentifierPathParam = false;
        foreach ($pathItem->getGet()->getParameters() as $parameter) {
            if ($parameter->getName() === 'animal_uuid' && $parameter->getIn() === 'path') {
                $hasIdentifierPathParam = true;
            }
        }

        $this->assertTrue($hasIdentifierPathParam, 'View operation must expose the identifier as a path parameter.');
    }

    public function testUpdateResourceControllerProducesPatchOperationWithBodyAndIdentifier(): void
    {
        $generator = new PathItemGenerator();

        $route = UpdateAnimalController::getRoute();

        $pathItem = $generator->generate([
            ['route' => $route, 'controllerClass' => UpdateAnimalController::class],
        ]);

        $this->assertNotNull($pathItem->getPatch());
        $this->assertNotNull($pathItem->getPatch()->getRequestBody());

        $hasIdentifierPathParam = false;
        foreach ($pathItem->getPatch()->getParameters() as $parameter) {
            if ($parameter->getName() === 'animal_uuid' && $parameter->getIn() === 'path') {
                $hasIdentifierPathParam = true;
            }
        }

        $this->assertTrue($hasIdentifierPathParam);
    }

    public function testDeleteResourceControllerProducesDeleteOperationWith204Response(): void
    {
        $generator = new PathItemGenerator();

        $route = DeleteAnimalController::getRoute();

        $pathItem = $generator->generate([
            ['route' => $route, 'controllerClass' => DeleteAnimalController::class],
        ]);

        $this->assertNotNull($pathItem->getDelete());

        $statusCodes = [];
        foreach ($pathItem->getDelete()->getResponses() as $response) {
            $statusCodes[] = $response->getStatusCode();
        }

        $this->assertContains(204, $statusCodes, 'Delete operation must declare a 204 (DeletedApivalkResponse) status.');
    }

    public function testListResourceControllerExposesPaginationQueryParameters(): void
    {
        $generator = new PathItemGenerator();

        $route = ListAnimalsController::getRoute();

        $pathItem = $generator->generate([
            ['route' => $route, 'controllerClass' => ListAnimalsController::class],
        ]);

        $names = [];
        foreach ($pathItem->getGet()->getParameters() as $parameter) {
            if ($parameter->getIn() === 'query') {
                $names[] = $parameter->getName();
            }
        }

        $this->assertContains('limit', $names);
        $this->assertContains('page', $names);
    }
}
