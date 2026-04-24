<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Population\Strategy;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\Strategy\QueryParameterPopulationStrategy;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class QueryParameterPopulationStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function testKnownQueryPropertyIsPopulated(): void
    {
        $_GET['search'] = 'cat';

        $doc = new ApivalkRequestDocumentation();
        $doc->addQueryProperty(new StringProperty('search'));

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new QueryParameterPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, $doc));

        self::assertNotNull($request->query()->get('search'));
        self::assertSame('cat', $request->query()->get('search')->getValue());
    }

    public function testUnknownQueryParameterIsIgnored(): void
    {
        $_GET['unknown'] = 'foo';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new QueryParameterPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertNull($request->query()->get('unknown'));
    }

    public function testOrderByIsNoLongerHandledByQueryBag(): void
    {
        $_GET['order_by'] = 'name';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new QueryParameterPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertNull($request->query()->get('order_by'));
    }

    private function makeRequest(): AbstractApivalkRequest
    {
        return new class extends AbstractApivalkRequest {
            /** @var ParameterBag */
            private $queryBag;

            public function __construct()
            {
                $this->queryBag = new ParameterBag();
            }

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }

            public function setQueryParameterBag(ParameterBag $bag): void
            {
                $this->queryBag = $bag;
            }

            public function query(): ParameterBag
            {
                return $this->queryBag;
            }
        };
    }
}
