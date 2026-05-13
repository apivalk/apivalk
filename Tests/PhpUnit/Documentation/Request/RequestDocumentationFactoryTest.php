<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Request;

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
    public function getMethod(): \apivalk\apivalk\Http\Method\MethodInterface { return new GetMethod(); }
    public function header(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return new \apivalk\apivalk\Http\Request\Parameter\ParameterBag(); }
    public function query(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return new \apivalk\apivalk\Http\Request\Parameter\ParameterBag(); }
    public function body(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return new \apivalk\apivalk\Http\Request\Parameter\ParameterBag(); }
    public function path(): \apivalk\apivalk\Http\Request\Parameter\ParameterBag { return new \apivalk\apivalk\Http\Request\Parameter\ParameterBag(); }
    public function file(): \apivalk\apivalk\Http\Request\File\FileBag { return new \apivalk\apivalk\Http\Request\File\FileBag(); }
    public function getAuthIdentity(): \apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity { return new \apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity([]); }
    public function setAuthIdentity(\apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity $authIdentity): void {}
    public function getIp(): ?string { return null; }
    public function getRateLimitResult(): ?\apivalk\apivalk\Router\RateLimit\RateLimitResult { return null; }
    public function setRateLimitResult(\apivalk\apivalk\Router\RateLimit\RateLimitResult $rateLimitResult): void {}
    public function getLocale(): \apivalk\apivalk\Http\i18n\Locale { return \apivalk\apivalk\Http\i18n\Locale::en(); }
    public function setLocale(\apivalk\apivalk\Http\i18n\Locale $locale): void {}
    public function sorting(): \apivalk\apivalk\Router\Route\Sort\SortBag { return new \apivalk\apivalk\Router\Route\Sort\SortBag(); }
    public function filtering(): \apivalk\apivalk\Router\Route\Filter\FilterBag { return new \apivalk\apivalk\Router\Route\Filter\FilterBag(); }
    public function paginator() { return null; }
    public function setIp(?string $ip): void {}
}

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

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
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
