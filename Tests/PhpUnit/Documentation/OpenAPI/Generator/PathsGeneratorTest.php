<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\File\FileBagFactory;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\PathsGenerator;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use PHPUnit\Framework\TestCase;

/**
 * @extends AbstractApivalkController<PathsTestRequest>
 */
class PathsTestController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/test', new GetMethod());
    }

    public static function getRequestClass(): string
    {
        return PathsTestRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [];
    }

    public function __invoke(PathsTestRequest $request): AbstractApivalkResponse
    {
        return new class extends AbstractApivalkResponse {
            public static function getDocumentation(): ApivalkResponseDocumentation
            {
                return new ApivalkResponseDocumentation();
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

class PathsTestRequest implements ApivalkRequestInterface
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

    public function getMethod(): MethodInterface
    {
        return new GetMethod();
    }

    public function header(): ParameterBag
    {
        return ParameterBagFactory::createHeaderBag();
    }

    public function query(): ParameterBag
    {
        return ParameterBagFactory::createQueryBag(
            new Route('', new GetMethod()),
            self::getDocumentation()->getQueryProperties()
        );
    }

    public function body(): ParameterBag
    {
        return ParameterBagFactory::createBodyBag(
            self::getDocumentation()->getBodyProperties()
        );
    }

    public function path(): ParameterBag
    {
        return ParameterBagFactory::createPathBag(
            new Route('', new GetMethod()),
            self::getDocumentation()->getPathProperties()
        );
    }

    public function file(): FileBag
    {
        return FileBagFactory::create();
    }

    public function getAuthIdentity(): AbstractAuthIdentity
    {
        return new GuestAuthIdentity([]);
    }

    public function setAuthIdentity(AbstractAuthIdentity $authIdentity): void
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

    public function filtering(): FilterBag
    {
        return new FilterBag();
    }

    public function paginator()
    {
        return new Pagination('page');
    }
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
        $route->method('getRouteAuthorization')->willReturn(null);
        $route->method('getFilters')->willReturn([]);

        $routes = [
            ['route' => $route, 'controllerClass' => PathsTestController::class]
        ];

        $paths = $generator->generate('/test', $routes);
        $this->assertArrayHasKey('/test', $paths->toArray());
    }
}
