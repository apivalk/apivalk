<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Population\Strategy;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Parameter\Parameter;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\Strategy\FilteringPopulationStrategy;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class FilteringPopulationStrategyTest extends TestCase
{
    public function testFiltersWithNoQueryParamsHaveNullValues(): void
    {
        $property = new StringProperty('status');
        $filter = StringFilter::equals($property);

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);
        $route->filtering([$filter]);

        $queryBag = new ParameterBag();
        $request = $this->makeRequest($queryBag);

        $strategy = new FilteringPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        $filterBag = $request->filtering();
        self::assertTrue($filterBag->has('status'));
        self::assertNull($filterBag->get('status')->getValue());
    }

    public function testFiltersPickUpValueFromQueryBag(): void
    {
        $property = new StringProperty('status');
        $filter = StringFilter::equals($property);

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);
        $route->filtering([$filter]);

        $queryBag = new ParameterBag();
        $queryBag->set(new Parameter('status', 'active', 'active'));
        $request = $this->makeRequest($queryBag);

        $strategy = new FilteringPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        $filterBag = $request->filtering();
        self::assertSame('active', $filterBag->get('status')->getValue());
    }

    public function testFilterCloneDoesNotMutateOriginalRoute(): void
    {
        $property = new StringProperty('status');
        $filter = StringFilter::equals($property);

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);
        $route->filtering([$filter]);

        $queryBag = new ParameterBag();
        $queryBag->set(new Parameter('status', 'active', 'active'));
        $request = $this->makeRequest($queryBag);

        $strategy = new FilteringPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertNull($route->getFilters()[0]->getValue());
    }

    public function testEmptyRouteFiltersProducesEmptyFilterBag(): void
    {
        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest(new ParameterBag());

        $strategy = new FilteringPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertCount(0, $request->filtering());
    }

    private function makeRequest(ParameterBag $queryBag): AbstractApivalkRequest
    {
        return new class($queryBag) extends AbstractApivalkRequest {
            /** @var ParameterBag */
            private $queryBag;
            /** @var FilterBag */
            private $filterBag;

            public function __construct(ParameterBag $queryBag)
            {
                $this->queryBag = $queryBag;
                $this->filterBag = new FilterBag();
            }

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }

            public function query(): ParameterBag
            {
                return $this->queryBag;
            }

            public function setFilterBag(FilterBag $bag): void
            {
                $this->filterBag = $bag;
            }

            public function filtering(): FilterBag
            {
                return $this->filterBag;
            }
        };
    }
}
