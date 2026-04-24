<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Middleware\RequestValidationMiddleware;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use PHPUnit\Framework\TestCase;

class SortingValidationTest extends TestCase
{
    public function testValidSortFieldPassesValidation(): void
    {
        $doc = new ApivalkRequestDocumentation();
        $doc->addAvailableSortField('name');

        $sortBag = new SortBag();
        $sortBag->set(Sort::asc('name'));

        $request = $this->makeRequest($doc, $sortBag);
        $middleware = new RequestValidationMiddleware();
        $next = function () {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);

        self::assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testInvalidSortFieldFailsValidation(): void
    {
        $doc = new ApivalkRequestDocumentation();
        $doc->addAvailableSortField('name');

        $sortBag = new SortBag();
        $sortBag->set(Sort::asc('hacked_field'));

        $request = $this->makeRequest($doc, $sortBag);
        $middleware = new RequestValidationMiddleware();
        $next = function () {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);

        self::assertInstanceOf(BadValidationApivalkResponse::class, $response);
        /** @var BadValidationApivalkResponse $response */
        self::assertCount(1, $response->getErrors());
    }

    public function testNoAvailableSortFieldsSkipsValidation(): void
    {
        $doc = new ApivalkRequestDocumentation();

        $sortBag = new SortBag();
        $sortBag->set(Sort::asc('anything'));

        $request = $this->makeRequest($doc, $sortBag);
        $middleware = new RequestValidationMiddleware();
        $next = function () {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);

        self::assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testMultipleInvalidSortFieldsProduceMultipleErrors(): void
    {
        $doc = new ApivalkRequestDocumentation();
        $doc->addAvailableSortField('name');

        $sortBag = new SortBag();
        $sortBag->set(Sort::asc('bad1'));
        $sortBag->set(Sort::asc('bad2'));

        $request = $this->makeRequest($doc, $sortBag);
        $middleware = new RequestValidationMiddleware();
        $next = function () {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);

        self::assertInstanceOf(BadValidationApivalkResponse::class, $response);
        /** @var BadValidationApivalkResponse $response */
        self::assertCount(2, $response->getErrors());
    }

    private function makeRequest(ApivalkRequestDocumentation $doc, SortBag $sortBag): ApivalkRequestInterface
    {
        return new class($doc, $sortBag) implements ApivalkRequestInterface {
            private static $d;
            private static $sb;

            public function __construct($d, $sb)
            {
                self::$d = $d;
                self::$sb = $sb;
            }

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function populate(\apivalk\apivalk\Router\Route\Route $route, ApivalkRequestDocumentation $doc): void
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

            public function header(): ParameterBag { return new ParameterBag(); }
            public function query(): ParameterBag { return new ParameterBag(); }
            public function body(): ParameterBag { return new ParameterBag(); }
            public function path(): ParameterBag { return new ParameterBag(); }

            public function file(): \apivalk\apivalk\Http\Request\File\FileBag
            {
                return new \apivalk\apivalk\Http\Request\File\FileBag();
            }

            public function getAuthIdentity(): \apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity
            {
                return new GuestAuthIdentity([]);
            }

            public function setAuthIdentity(\apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity $i): void {}

            public function getIp(): string { return '127.0.0.1'; }

            public function getRateLimitResult(): ?RateLimitResult { return null; }
            public function setRateLimitResult(RateLimitResult $r): void {}

            public function getLocale(): Locale { return Locale::en(); }
            public function setLocale(Locale $l): void {}

            public function sorting(): SortBag { return self::$sb; }
            public function filtering(): FilterBag { return new FilterBag(); }
            public function paginator() { return null; }
        };
    }
}
