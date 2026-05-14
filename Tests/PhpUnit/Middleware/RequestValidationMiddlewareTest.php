<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\ByteProperty;
use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
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
use apivalk\apivalk\Router\Route\Filter\BinaryFilter;
use apivalk\apivalk\Router\Route\Filter\BooleanFilter;
use apivalk\apivalk\Router\Route\Filter\ByteFilter;
use apivalk\apivalk\Router\Route\Filter\DateFilter;
use apivalk\apivalk\Router\Route\Filter\DateTimeFilter;
use apivalk\apivalk\Router\Route\Filter\EnumFilter;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\FloatFilter;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
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

    public function testDateTimeFilterDoesNotFatalOnTypedValue(): void
    {
        $property = new DateTimeProperty('started_from', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = DateTimeFilter::greaterThan($property);
        $filter->setValue(new \DateTime('2024-01-15T14:30:00+00:00'));
        $filter->setRawValue('2024-01-15T14:30:00+00:00');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testDateTimeFilterReportsErrorForUnparseableInput(): void
    {
        $property = new DateTimeProperty('started_from', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = DateTimeFilter::greaterThan($property);
        $filter->setRawValue('not-a-datetime');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        /** @var BadValidationApivalkResponse $response */
        $this->assertCount(1, $response->getErrors());
    }

    public function testDateFilterDoesNotFatalOnTypedValue(): void
    {
        $property = new DateProperty('born_on', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = DateFilter::equals($property);
        $filter->setValue(new \DateTime('2024-01-15'));
        $filter->setRawValue('2024-01-15');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testDateFilterReportsErrorForUnparseableInput(): void
    {
        $property = new DateProperty('born_on', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = DateFilter::equals($property);
        $filter->setRawValue('15/01/2024');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testIntegerFilterPassesValidValue(): void
    {
        $property = new IntegerProperty('age', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = IntegerFilter::equals($property);
        $filter->setValue(42);
        $filter->setRawValue('42');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testFloatFilterPassesValidValue(): void
    {
        $property = new FloatProperty('rating', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = FloatFilter::equals($property);
        $filter->setValue(4.5);
        $filter->setRawValue('4.5');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testBooleanFilterPassesValidValue(): void
    {
        $property = new BooleanProperty('is_active', '', false);
        $property->init();
        $property->setIsRequired(false);

        $filter = BooleanFilter::equals($property);
        $filter->setValue(true);
        $filter->setRawValue('true');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testEnumFilterPassesValidValue(): void
    {
        $property = new EnumProperty('status', '', ['active', 'inactive']);
        $property->init();
        $property->setIsRequired(false);

        $filter = EnumFilter::equals($property);
        $filter->setValue('active');
        $filter->setRawValue('active');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testEnumFilterRejectsValueOutsideAllowedSet(): void
    {
        $property = new EnumProperty('status', '', ['active', 'inactive']);
        $property->init();
        $property->setIsRequired(false);

        $filter = EnumFilter::equals($property);
        $filter->setValue('archived');
        $filter->setRawValue('archived');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testBinaryFilterPassesValidValue(): void
    {
        $property = new BinaryProperty('blob', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = BinaryFilter::equals($property);
        $filter->setValue('payload');
        $filter->setRawValue('payload');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testByteFilterPassesValidValue(): void
    {
        $property = new ByteProperty('blob', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = ByteFilter::equals($property);
        $base64 = base64_encode('payload');
        $filter->setValue($base64);
        $filter->setRawValue($base64);

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testRawValueOnlyTriggersValidation(): void
    {
        $property = new StringProperty('status', '');
        $property->setIsRequired(false);

        $validator = $this->createMock(AbstractValidator::class);
        $validator->method('validate')->willReturn(new ValidatorResult(false, 'Invalid value'));
        $property->addValidator($validator);

        $filter = StringFilter::equals($property);
        $filter->setRawValue('something');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testEnumFilterRejectsInvalidValueViaFactoryAlone(): void
    {
        $property = new EnumProperty('status', '', ['active', 'inactive']);
        $property->setIsRequired(false);
        // no $property->init() — the filter constructor must register the validator

        $filter = EnumFilter::equals($property);
        $filter->setValue('archived');
        $filter->setRawValue('archived');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        /** @var BadValidationApivalkResponse $response */
        $this->assertCount(1, $response->getErrors());
    }

    public function testIntegerFilterRejectsValueBelowMinimumViaFactoryAlone(): void
    {
        $property = new IntegerProperty('age', '');
        $property->setMinimumValue(0);
        $property->setIsRequired(false);
        // no $property->init()

        $filter = IntegerFilter::greaterThan($property);
        $filter->setValue(-1);
        $filter->setRawValue('-1');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testStringFilterRejectsValueExceedingMaxLengthViaFactoryAlone(): void
    {
        $property = new StringProperty('name', '');
        $property->setMaxLength(5);
        $property->setIsRequired(false);
        // no $property->init()

        $filter = StringFilter::equals($property);
        $filter->setValue('toolongvalue');
        $filter->setRawValue('toolongvalue');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testDateTimeFilterRejectsInvalidInputViaFactoryAlone(): void
    {
        $property = new DateTimeProperty('created_at', '');
        $property->setIsRequired(false);
        // no $property->init()

        $filter = DateTimeFilter::greaterThan($property);
        $filter->setRawValue('not-a-datetime');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    private function runFilterMiddleware(FilterInterface $filter): AbstractApivalkResponse
    {
        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = function ($req) {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        return (new RequestValidationMiddleware())->process(
            $request,
            $this->createMock(AbstractApivalkController::class),
            $next
        );
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
