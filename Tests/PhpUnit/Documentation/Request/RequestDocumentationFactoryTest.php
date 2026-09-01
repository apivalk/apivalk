<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Request;

use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Documentation\Request\RequestDocumentationFactory;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class FactoryTestRequest implements ApivalkRequestInterface
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        $doc = new ApivalkRequestDocumentation();
        $doc->addPathProperty(new StringProperty('slug', 'Slug'));
        return $doc;
    }

    public function populate(Route $route, ApivalkRequestDocumentation $documentation): void {}
    public function getRuntimeDocumentation(): ApivalkRequestDocumentation { return self::getDocumentation(); }
    public function getMethod(): MethodInterface { return new GetMethod(); }
    public function header(): ParameterBag { return new ParameterBag(); }
    public function query(): ParameterBag { return new ParameterBag(); }
    public function body(): ParameterBag { return new ParameterBag(); }
    public function path(): ParameterBag { return new ParameterBag(); }
    public function file(): FileBag { return new FileBag(); }
    public function getAuthIdentity(): AbstractAuthIdentity { return new GuestAuthIdentity([]); }
    public function setAuthIdentity(AbstractAuthIdentity $authIdentity): void {}
    public function getIp(): ?string { return null; }
    public function getRateLimitResult(): ?RateLimitResult { return null; }
    public function setRateLimitResult(RateLimitResult $rateLimitResult): void {}
    public function getLocale(): Locale { return Locale::en(); }
    public function setLocale(Locale $locale): void {}
    public function sorting(): SortBag { return new SortBag(); }
    public function filtering(): FilterBag { return new FilterBag(); }
    public function paginator() { return null; }
    public function setIp(?string $ip): void {}
}

/**
 * @extends AbstractApivalkController<FactoryTestRequest>
 */
class FactoryTestController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::get('/items/{id}')->pathProperty(new IntegerProperty('id', 'ID'));
    }

    public static function getRequestClass(): string
    {
        return FactoryTestRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [];
    }

    public function __invoke(FactoryTestRequest $request): AbstractApivalkResponse
    {
        return new class extends AbstractApivalkResponse {
            public static function getDocumentation(): ApivalkResponseDocumentation { return new ApivalkResponseDocumentation(); }
            public static function getStatusCode(): int { return 200; }
            public function toArray(): array { return []; }
        };
    }
}

class RequestDocumentationFactoryTest extends TestCase
{
    public function testBuildRuntimeDocumentationMergesRoutePathProperties(): void
    {
        $route = Route::get('/items/{id}')->pathProperty(new IntegerProperty('id', 'ID'));

        $documentation = RequestDocumentationFactory::buildRuntimeDocumentation($route, FactoryTestController::class);

        $pathProperties = $documentation->getPathProperties();

        $this->assertArrayHasKey('slug', $pathProperties);
        $this->assertArrayHasKey('id', $pathProperties);
    }

    public function testBuildRuntimeDocumentationWithoutRoutePathProperties(): void
    {
        $route = Route::get('/items/{slug}');

        $documentation = RequestDocumentationFactory::buildRuntimeDocumentation($route, FactoryTestController::class);

        $pathProperties = $documentation->getPathProperties();
        $this->assertArrayHasKey('slug', $pathProperties);
        $this->assertArrayNotHasKey('id', $pathProperties);
    }
}
