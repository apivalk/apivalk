<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Population\Strategy;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\Strategy\SortingPopulationStrategy;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class SortingPopulationStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        unset($_GET['order_by']);
    }

    protected function tearDown(): void
    {
        unset($_GET['order_by']);
    }

    public function testPopulatesDefaultSortingsFromRoute(): void
    {
        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);
        $route->sorting([Sort::asc('name'), Sort::desc('weight')]);

        $request = $this->makeRequest();
        $strategy = new SortingPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        $bag = $request->sorting();
        self::assertTrue($bag->has('name'));
        self::assertTrue($bag->has('weight'));
        self::assertTrue($bag->get('name')->isAsc());
        self::assertTrue($bag->get('weight')->isDesc());
    }

    public function testOrderByQueryParamOverridesDirection(): void
    {
        $_GET['order_by'] = '-name';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);
        $route->sorting([Sort::asc('name')]);

        $request = $this->makeRequest();
        $strategy = new SortingPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        $bag = $request->sorting();
        self::assertTrue($bag->has('name'));
        self::assertTrue($bag->get('name')->isDesc());
    }

    public function testOrderByQueryParamWithPlusPrefix(): void
    {
        $_GET['order_by'] = '+name';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new SortingPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        $bag = $request->sorting();
        self::assertTrue($bag->has('name'));
        self::assertTrue($bag->get('name')->isAsc());
    }

    public function testOrderByQueryParamWithoutPrefix(): void
    {
        $_GET['order_by'] = 'name';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new SortingPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        $bag = $request->sorting();
        self::assertTrue($bag->has('name'));
        self::assertTrue($bag->get('name')->isAsc());
    }

    public function testMultipleFieldsInOrderBy(): void
    {
        $_GET['order_by'] = '+name,-weight';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new SortingPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        $bag = $request->sorting();
        self::assertTrue($bag->has('name'));
        self::assertTrue($bag->has('weight'));
        self::assertTrue($bag->get('name')->isAsc());
        self::assertTrue($bag->get('weight')->isDesc());
    }

    public function testEmptyOrderByIsIgnored(): void
    {
        $_GET['order_by'] = '';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new SortingPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertCount(0, $request->sorting());
    }

    public function testNoOrderByLeavesRouteDefaults(): void
    {
        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);
        $route->sorting([Sort::asc('name')]);

        $request = $this->makeRequest();
        $strategy = new SortingPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertCount(1, $request->sorting());
    }

    private function makeRequest(): AbstractApivalkRequest
    {
        return new class extends AbstractApivalkRequest {
            /** @var SortBag */
            private $sortBag;

            public function __construct()
            {
                $this->sortBag = new SortBag();
            }

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }

            public function setSortBag(SortBag $bag): void
            {
                $this->sortBag = $bag;
            }

            public function sorting(): SortBag
            {
                return $this->sortBag;
            }
        };
    }
}
