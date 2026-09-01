<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
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
use apivalk\apivalk\Router\Route\Filter\Operator;
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

            public function populate(Route $route, ApivalkRequestDocumentation $documentation): void
            {
            }

            public function getRuntimeDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function getMethod(): MethodInterface
            {
                return $this->createMock(MethodInterface::class);
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

            public function file(): FileBag
            {
                return new FileBag();
            }

            public function getAuthIdentity(): AbstractAuthIdentity
            {
                return new GuestAuthIdentity([]);
            }

            public function setAuthIdentity(AbstractAuthIdentity $authIdentity
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
                return new FilterBag();
            }

            public function paginator()
            {
                return new Pagination('page');
            }
        };

        $next = (fn($req) => $this->createMock(AbstractApivalkResponse::class));

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

            public function populate(Route $route, ApivalkRequestDocumentation $documentation): void
            {
            }

            public function getRuntimeDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function getMethod(): MethodInterface
            {
                return $this->createMock(MethodInterface::class);
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

            public function file(): FileBag
            {
                return new FileBag();
            }

            public function getAuthIdentity(): AbstractAuthIdentity
            {
                return new GuestAuthIdentity([]);
            }

            public function setAuthIdentity(AbstractAuthIdentity $authIdentity
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
                return new FilterBag();
            }

            public function paginator()
            {
                return new Pagination('page');
            }
        };

        $next = (fn($req) => $this->createMock(AbstractApivalkResponse::class));

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

        $filter = new StringFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 'active', null);

        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = (fn($req) => $this->createMock(AbstractApivalkResponse::class));

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

        $filter = new StringFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 'invalid', null);

        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = (fn($req) => $this->createMock(AbstractApivalkResponse::class));

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

        $filter = new StringFilter($property, Operator::EQ);

        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = (fn($req) => $this->createMock(AbstractApivalkResponse::class));

        $response = $middleware->process($request, $this->createMock(AbstractApivalkController::class), $next);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testFilterValidationFailsWhenRequiredAndNull(): void
    {
        $middleware = new RequestValidationMiddleware();

        $property = new StringProperty('status');
        $property->setIsRequired(true);

        $filter = new StringFilter($property, Operator::EQ);

        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = (fn($req) => $this->createMock(AbstractApivalkResponse::class));

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

        $filter = new DateTimeFilter($property, Operator::GT);
        $filter->setCondition(Operator::GT, new \DateTime('2024-01-15T14:30:00+00:00'), '2024-01-15T14:30:00+00:00');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testDateTimeFilterReportsErrorForUnparseableInput(): void
    {
        $property = new DateTimeProperty('started_from', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = new DateTimeFilter($property, Operator::GT);
        $filter->setCondition(Operator::GT, null, 'not-a-datetime');

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

        $filter = new DateFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, new \DateTime('2024-01-15'), '2024-01-15');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testDateFilterReportsErrorForUnparseableInput(): void
    {
        $property = new DateProperty('born_on', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = new DateFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, null, '15/01/2024');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testIntegerFilterPassesValidValue(): void
    {
        $property = new IntegerProperty('age', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = new IntegerFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 42, '42');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testFloatFilterPassesValidValue(): void
    {
        $property = new FloatProperty('rating', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = new FloatFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 4.5, '4.5');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testBooleanFilterPassesValidValue(): void
    {
        $property = new BooleanProperty('is_active', '', false);
        $property->init();
        $property->setIsRequired(false);

        $filter = new BooleanFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, true, 'true');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testEnumFilterPassesValidValue(): void
    {
        $property = new EnumProperty('status', '', ['active', 'inactive']);
        $property->init();
        $property->setIsRequired(false);

        $filter = new EnumFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 'active', 'active');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testEnumFilterRejectsValueOutsideAllowedSet(): void
    {
        $property = new EnumProperty('status', '', ['active', 'inactive']);
        $property->init();
        $property->setIsRequired(false);

        $filter = new EnumFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 'archived', 'archived');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testBinaryFilterPassesValidValue(): void
    {
        $property = new BinaryProperty('blob', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = new BinaryFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 'payload', 'payload');

        $response = $this->runFilterMiddleware($filter);
        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testByteFilterPassesValidValue(): void
    {
        $property = new ByteProperty('blob', '');
        $property->init();
        $property->setIsRequired(false);

        $filter = new ByteFilter($property, Operator::EQ);
        $base64 = base64_encode('payload');
        $filter->setCondition(Operator::EQ, $base64, $base64);

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

        $filter = new StringFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, null, 'something');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testEnumFilterRejectsInvalidValueViaFactoryAlone(): void
    {
        $property = new EnumProperty('status', '', ['active', 'inactive']);
        $property->setIsRequired(false);
        // no $property->init() — the filter constructor must register the validator

        $filter = new EnumFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 'archived', 'archived');

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

        $filter = new IntegerFilter($property, Operator::GT);
        $filter->setCondition(Operator::GT, -1, '-1');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testStringFilterRejectsValueExceedingMaxLengthViaFactoryAlone(): void
    {
        $property = new StringProperty('name', '');
        $property->setMaxLength(5);
        $property->setIsRequired(false);
        // no $property->init()

        $filter = new StringFilter($property, Operator::EQ);
        $filter->setCondition(Operator::EQ, 'toolongvalue', 'toolongvalue');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testDateTimeFilterRejectsInvalidInputViaFactoryAlone(): void
    {
        $property = new DateTimeProperty('created_at', '');
        $property->setIsRequired(false);
        // no $property->init()

        $filter = new DateTimeFilter($property, Operator::GT);
        $filter->setCondition(Operator::GT, null, 'not-a-datetime');

        $response = $this->runFilterMiddleware($filter);
        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    private function runFilterMiddleware(FilterInterface $filter): AbstractApivalkResponse
    {
        $filterBag = new FilterBag();
        $filterBag->set($filter);

        $request = $this->createRequest(new ApivalkRequestDocumentation(), $filterBag);

        $next = (fn($req) => $this->createMock(AbstractApivalkResponse::class));

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

            public function populate(Route $route, ApivalkRequestDocumentation $documentation): void
            {
            }

            public function getRuntimeDocumentation(): ApivalkRequestDocumentation
            {
                return self::$d;
            }

            public function getMethod(): MethodInterface
            {
                return new class implements MethodInterface {
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

            public function file(): FileBag
            {
                return new FileBag();
            }

            public function getAuthIdentity(): AbstractAuthIdentity
            {
                return new GuestAuthIdentity([]);
            }

            public function setAuthIdentity(AbstractAuthIdentity $authIdentity
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
