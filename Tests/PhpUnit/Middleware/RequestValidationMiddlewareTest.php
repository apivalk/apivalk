<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Documentation\Property\Validator\AbstractValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\Parameter;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Middleware\RequestValidationMiddleware;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use PHPUnit\Framework\TestCase;

class RequestValidationMiddlewareTest extends TestCase
{
    public function testProcessSuccess(): void
    {
        $middleware = new RequestValidationMiddleware();

        $doc = new ApivalkRequestDocumentation();
        $prop = new class('test') extends AbstractProperty {
            public function getType(): string
            {
                return 'string';
            }

            public function getPhpType(): string
            {
                return 'string';
            }

            public function getDocumentationArray(): array
            {
                return [];
            }
        };
        $prop->setIsRequired(true);

        $doc->addQueryProperty($prop);

        $request = new class($doc) implements ApivalkRequestInterface {
            private static $d;

            public function __construct($d)
            {
                self::$d = $d;
            }

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function populate(\apivalk\apivalk\Router\Route\Route $route, ApivalkRequestDocumentation $documentation): void
            {
            }

            public function getRuntimeDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function getMethod(): \apivalk\apivalk\Http\Method\MethodInterface
            {
                return $this->createMock(\apivalk\apivalk\Http\Method\MethodInterface::class);
            }

            public function header(): ParameterBag
            {
                return new ParameterBag();
            }

            public function query(): ParameterBag
            {
                $bag = new ParameterBag();
                $bag->set(new Parameter('test', 'val', 'val'));
                return $bag;
            }

            public function body(): ParameterBag
            {
                return new ParameterBag();
            }

            public function path(): ParameterBag
            {
                return new ParameterBag();
            }

            public function file(): \apivalk\apivalk\Http\Request\File\FileBag
            {
                return new \apivalk\apivalk\Http\Request\File\FileBag();
            }

            public function getAuthIdentity(): \apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity
            {
                return new GuestAuthIdentity([]);
            }

            public function setAuthIdentity(\apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity $authIdentity
            ): void {
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
        };

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testProcessValidationError(): void
    {
        $middleware = new RequestValidationMiddleware();

        $doc = new ApivalkRequestDocumentation();
        $prop = new class('test') extends AbstractProperty {
            public function getType(): string
            {
                return 'string';
            }

            public function getPhpType(): string
            {
                return 'string';
            }

            public function getDocumentationArray(): array
            {
                return [];
            }
        };
        $prop->setIsRequired(true);

        $validator = $this->createMock(AbstractValidator::class);
        $validator->method('validate')->willReturn(new ValidatorResult(false, 'Invalid value'));
        $prop->addValidator($validator);

        $doc->addBodyProperty($prop);

        $request = new class($doc) implements ApivalkRequestInterface {
            private static $d;

            public function __construct($d)
            {
                self::$d = $d;
            }

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function populate(\apivalk\apivalk\Router\Route\Route $route, ApivalkRequestDocumentation $documentation): void
            {
            }

            public function getRuntimeDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function getMethod(): \apivalk\apivalk\Http\Method\MethodInterface
            {
                return $this->createMock(\apivalk\apivalk\Http\Method\MethodInterface::class);
            }

            public function header(): ParameterBag
            {
                return new ParameterBag();
            }

            public function query(): ParameterBag
            {
                return new ParameterBag();
            }

            public function body(): ParameterBag
            {
                $bag = new ParameterBag();
                $bag->set(new Parameter('test', 'val', 'val'));
                return $bag;
            }

            public function path(): ParameterBag
            {
                return new ParameterBag();
            }

            public function file(): \apivalk\apivalk\Http\Request\File\FileBag
            {
                return new \apivalk\apivalk\Http\Request\File\FileBag();
            }

            public function getAuthIdentity(): \apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity
            {
                return new GuestAuthIdentity([]);
            }

            public function setAuthIdentity(\apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity $authIdentity
            ): void {
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
        };

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        /** @var BadValidationApivalkResponse $response */
        $this->assertCount(1, $response->getErrors());
    }

    public function testFilterValidationSuccess(): void
    {
        $middleware = new RequestValidationMiddleware();

        $property = new StringProperty('status');
        $property->setIsRequired(false);

        $filter = StringFilter::equals($property);
        $filter->setValue('active');

        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testFilterValidationFailsWithInvalidValue(): void
    {
        $middleware = new RequestValidationMiddleware();

        $property = new StringProperty('status');
        $property->setIsRequired(false);

        $validator = $this->createMock(AbstractValidator::class);
        $validator->method('validate')->willReturn(new ValidatorResult(false, 'Invalid value'));
        $property->addValidator($validator);

        $filter = StringFilter::equals($property);
        $filter->setValue('invalid');

        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        /** @var BadValidationApivalkResponse $response */
        $this->assertCount(1, $response->getErrors());
    }

    public function testFilterValidationSkipsNullValueWhenNotRequired(): void
    {
        $middleware = new RequestValidationMiddleware();

        $property = new StringProperty('status');
        $property->setIsRequired(false);

        $validator = $this->createMock(AbstractValidator::class);
        $validator->method('validate')->willReturn(new ValidatorResult(false, 'Invalid value'));
        $property->addValidator($validator);

        $filter = StringFilter::equals($property);

        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testFilterValidationFailsWhenRequiredAndNull(): void
    {
        $middleware = new RequestValidationMiddleware();

        $property = new StringProperty('status');
        $property->setIsRequired(true);

        $filter = StringFilter::equals($property);

        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        /** @var BadValidationApivalkResponse $response */
        $this->assertCount(1, $response->getErrors());
    }

    private function createRequest(
        ApivalkRequestDocumentation $doc,
        ?FilterBag $filterBag = null
    ): ApivalkRequestInterface {
        $fb = $filterBag ?? new FilterBag();

        return new class($doc, $fb) implements ApivalkRequestInterface {
            private static $d;
            private static $fb;

            public function __construct($d, $fb)
            {
                self::$d = $d;
                self::$fb = $fb;
            }

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function populate(\apivalk\apivalk\Router\Route\Route $route, ApivalkRequestDocumentation $documentation): void
            {
            }

            public function getRuntimeDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function getMethod(): \apivalk\apivalk\Http\Method\MethodInterface
            {
                return new class implements \apivalk\apivalk\Http\Method\MethodInterface {
                    public function getMethod(): string
                    {
                        return 'GET';
                    }
                };
            }

            public function header(): ParameterBag
            {
                return new ParameterBag();
            }

            public function query(): ParameterBag
            {
                return new ParameterBag();
            }

            public function body(): ParameterBag
            {
                return new ParameterBag();
            }

            public function path(): ParameterBag
            {
                return new ParameterBag();
            }

            public function file(): \apivalk\apivalk\Http\Request\File\FileBag
            {
                return new \apivalk\apivalk\Http\Request\File\FileBag();
            }

            public function getAuthIdentity(): \apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity
            {
                return new GuestAuthIdentity([]);
            }

            public function setAuthIdentity(\apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity $authIdentity
            ): void {
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
                return self::$fb;
            }

            public function paginator()
            {
                return new Pagination('page');
            }
        };
    }
}
