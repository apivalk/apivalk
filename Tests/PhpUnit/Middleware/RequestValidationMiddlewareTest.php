<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\Validator\AbstractValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\Parameter;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Middleware\RequestValidationMiddleware;
use apivalk\apivalk\Router\RateLimit\RateLimitResult;
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

            public function populate(\apivalk\apivalk\Router\Route $route): void
            {
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
                $bag->set(new Parameter('test', 'val'));
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

            public function populate(\apivalk\apivalk\Router\Route $route): void
            {
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
                $bag->set(new Parameter('test', 'val'));
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
        };

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        /** @var BadValidationApivalkResponse $response */
        $this->assertCount(1, $response->getErrors());
    }
}
