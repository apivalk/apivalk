<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Integration;

use apivalk\apivalk\Apivalk;
use apivalk\apivalk\ApivalkConfiguration;
use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactoryInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Middleware\RequestValidationMiddleware;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Router\Route\Filter\DateTimeFilter;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\RouteJsonSerializer;
use apivalk\apivalk\Router\Route\RouteRegexFactory;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Router\Router;
use apivalk\apivalk\Util\ClassLocator;
use PHPUnit\Framework\TestCase;

class ListAnimalsIntegrationTest extends TestCase
{
    /** @var array<string, mixed> */
    private $serverBackup = [];
    /** @var array<string, mixed> */
    private $getBackup = [];

    public static function setUpBeforeClass(): void
    {
        if (class_exists('ListAnimalsIntegrationTestController', false)) {
            return;
        }

        // Eval'd into the global namespace so that ::class strings resolve cleanly when
        // RouteJsonSerializer round-trips controllerClass through the cache layer.
        eval(<<<'PHP'
            class ListAnimalsIntegrationTestRequest extends \apivalk\apivalk\Http\Request\AbstractApivalkRequest {
                public static function getDocumentation(): \apivalk\apivalk\Documentation\ApivalkRequestDocumentation {
                    return new \apivalk\apivalk\Documentation\ApivalkRequestDocumentation();
                }
            }

            class ListAnimalsIntegrationTestResponse extends \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
                public static function getDocumentation(): \apivalk\apivalk\Documentation\ApivalkResponseDocumentation {
                    return new \apivalk\apivalk\Documentation\ApivalkResponseDocumentation();
                }
                public static function getStatusCode(): int { return 200; }
                public function toArray(): array { return ['data' => []]; }
            }

            class ListAnimalsIntegrationTestController extends \apivalk\apivalk\Http\Controller\AbstractApivalkController {
                public static $wasInvoked = false;
                public static $capturedRequest = null;

                public static function reset(): void {
                    self::$wasInvoked = false;
                    self::$capturedRequest = null;
                }

                public function __invoke(\apivalk\apivalk\Http\Request\ApivalkRequestInterface $request): \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
                    self::$wasInvoked = true;
                    self::$capturedRequest = $request;
                    return new ListAnimalsIntegrationTestResponse();
                }

                public static function getRoute(): \apivalk\apivalk\Router\Route\Route {
                    return \apivalk\apivalk\Router\Route\Route::get('/animals');
                }
                public static function getRequestClass(): string { return 'ListAnimalsIntegrationTestRequest'; }
                public static function getResponseClasses(): array { return ['ListAnimalsIntegrationTestResponse']; }
            }
PHP
        );
    }

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->getBackup = $_GET;

        \ListAnimalsIntegrationTestController::reset();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_GET = $this->getBackup;
    }

    public function testHappyPathPopulatesFiltersSortAndPagination(): void
    {
        $_GET = [
            'species' => 'cat',
            'min_weight_kg' => '5',
            'born_after' => '2020-01-15T00:00:00+00:00',
            'order_by' => '+name,-id',
            'page' => '2',
        ];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/animals';

        $response = $this->dispatch();

        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
        $this->assertTrue(\ListAnimalsIntegrationTestController::$wasInvoked);

        $request = \ListAnimalsIntegrationTestController::$capturedRequest;
        $this->assertNotNull($request);

        $species = $request->filtering()->__get('species');
        $this->assertNotNull($species);
        $this->assertSame('cat', $species->getValue());
        $this->assertSame('cat', $species->getRawValue());

        $minWeight = $request->filtering()->__get('min_weight_kg');
        $this->assertNotNull($minWeight);
        $this->assertSame(5, $minWeight->getValue());
        $this->assertSame('5', $minWeight->getRawValue());

        $bornAfter = $request->filtering()->__get('born_after');
        $this->assertNotNull($bornAfter);
        $this->assertInstanceOf(\DateTime::class, $bornAfter->getValue());
        $this->assertSame('2020-01-15T00:00:00+00:00', $bornAfter->getRawValue());
    }

    /**
     * Regression for the "(string) \DateTime" fatal in RequestValidationMiddleware.
     * A populated DateTimeFilter must not blow up the request lifecycle.
     */
    public function testDateTimeFilterDoesNotFatalDuringDispatch(): void
    {
        $_GET = ['born_after' => '2020-01-15T00:00:00+00:00'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/animals';

        $response = $this->dispatch();

        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
        $this->assertTrue(\ListAnimalsIntegrationTestController::$wasInvoked);
    }

    public function testInvalidDateTimeFilterReturnsValidationError(): void
    {
        $_GET = ['born_after' => 'not-a-datetime'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/animals';

        $response = $this->dispatch();

        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        $this->assertFalse(\ListAnimalsIntegrationTestController::$wasInvoked);
    }

    /**
     * Sort precedence behavior introduced in b857a7c: user-requested sorts come first
     * in iteration order, route defaults appended for fields the user didn't specify.
     */
    public function testUserSortsTakePrecedenceOverRouteDefaults(): void
    {
        $_GET = ['order_by' => '+name'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/animals';

        $response = $this->dispatch();

        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);

        $sortBag = \ListAnimalsIntegrationTestController::$capturedRequest->sorting();
        $sorts = iterator_to_array($sortBag, false);

        $this->assertCount(2, $sorts, 'Expected user sort + route default');
        $this->assertSame('name', $sorts[0]->getField());
        $this->assertTrue($sorts[0]->isAsc());
        $this->assertTrue($sorts[0]->isRequested());

        $this->assertSame('id', $sorts[1]->getField());
        $this->assertFalse($sorts[1]->isAsc());
        $this->assertFalse($sorts[1]->isRequested());

        $requested = $sortBag->getRequested();
        $this->assertCount(1, $requested);
        $this->assertSame('name', $requested[0]->getField());
    }

    /**
     * When the user sorts on the same field as a route default, the user's direction wins
     * AND the field appears at the position the user requested it.
     */
    public function testUserSortOverridesRouteDefaultOnSameField(): void
    {
        $_GET = ['order_by' => '+id'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/animals';

        $response = $this->dispatch();

        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);

        $sortBag = \ListAnimalsIntegrationTestController::$capturedRequest->sorting();
        $sorts = iterator_to_array($sortBag, false);

        // User's `id` first (asc, overriding the route's desc default), then route default `name`.
        $this->assertCount(2, $sorts);
        $this->assertSame('id', $sorts[0]->getField());
        $this->assertTrue($sorts[0]->isAsc(), 'User-supplied direction should override route default');
        $this->assertTrue($sorts[0]->isRequested());

        $this->assertSame('name', $sorts[1]->getField());
        $this->assertFalse($sorts[1]->isRequested());
    }

    /**
     * Sorting on a field not declared in the route's available sort fields
     * is rejected by RequestValidationMiddleware.
     */
    public function testUnknownSortFieldIsRejected(): void
    {
        $_GET = ['order_by' => '+unknown_field'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/animals';

        $response = $this->dispatch();

        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        $this->assertFalse(\ListAnimalsIntegrationTestController::$wasInvoked);
    }

    /**
     * Without ?order_by the route defaults are used and getRequested() is empty.
     */
    public function testRouteDefaultsApplyWhenUserDidNotRequestSort(): void
    {
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/animals';

        $response = $this->dispatch();

        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);

        $sortBag = \ListAnimalsIntegrationTestController::$capturedRequest->sorting();
        $sorts = iterator_to_array($sortBag, false);

        $this->assertCount(2, $sorts);
        $this->assertSame('name', $sorts[0]->getField());
        $this->assertTrue($sorts[0]->isAsc());
        $this->assertFalse($sorts[0]->isRequested());

        $this->assertSame('id', $sorts[1]->getField());
        $this->assertFalse($sorts[1]->isAsc());
        $this->assertFalse($sorts[1]->isRequested());

        $this->assertSame([], $sortBag->getRequested());
    }

    public function testIntegerFilterBelowMinimumReturnsValidationError(): void
    {
        $_GET = ['min_weight_kg' => '-5'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/animals';

        $response = $this->dispatch();

        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        $this->assertFalse(\ListAnimalsIntegrationTestController::$wasInvoked);
    }

    private function dispatch(): AbstractApivalkResponse
    {
        $route = self::buildAnimalsRoute();
        $controllerClass = 'ListAnimalsIntegrationTestController';

        $indexData = [
            [
                'regex' => RouteRegexFactory::build($route),
                'method' => 'GET',
                'key' => 'route_animals_list',
                'controllerClass' => $controllerClass,
            ],
        ];

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturnCallback(static function ($key) use ($indexData, $route) {
            if ($key === AbstractRouter::CACHE_INDEX_KEY) {
                return new CacheItem(AbstractRouter::CACHE_INDEX_KEY, json_encode($indexData));
            }
            if ($key === 'route_animals_list') {
                return new CacheItem('route_animals_list', json_encode(RouteJsonSerializer::serialize($route)));
            }
            return null;
        });

        $controllerFactory = $this->createMock(ApivalkControllerFactoryInterface::class);
        $controllerFactory->method('create')
            ->willReturnCallback(static function () {
                return new \ListAnimalsIntegrationTestController();
            });

        $router = new Router($this->createMock(ClassLocator::class), $cache, $controllerFactory);
        $apivalk = new Apivalk(new ApivalkConfiguration($router));
        $router->setApivalk($apivalk);

        $stack = new MiddlewareStack();
        $stack->add(new RequestValidationMiddleware());

        return $router->dispatch($stack);
    }

    private static function buildAnimalsRoute(): Route
    {
        $species = new StringProperty('species', '');
        $species->init();
        $minWeight = new IntegerProperty('min_weight_kg', '');
        $minWeight->setMinimumValue(0);
        $minWeight->init();
        $bornAfter = new DateTimeProperty('born_after', '');
        $bornAfter->init();

        return Route::get('/animals')
            ->filtering([
                StringFilter::equals($species),
                IntegerFilter::greaterThan($minWeight),
                DateTimeFilter::greaterThan($bornAfter),
            ])
            ->sorting([Sort::asc('name'), Sort::desc('id')])
            ->pagination(Pagination::page());
    }
}
