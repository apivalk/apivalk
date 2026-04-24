<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Population\Strategy;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Pagination\OffsetPaginator;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\Strategy\PaginationPopulationStrategy;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class PaginationPopulationStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        unset($_GET['page'], $_GET['per_page'], $_GET['limit'], $_GET['offset']);
    }

    protected function tearDown(): void
    {
        unset($_GET['page'], $_GET['per_page'], $_GET['limit'], $_GET['offset']);
    }

    public function testNoPaginationOnRouteLeavesPaginatorNull(): void
    {
        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);

        $request = $this->makeRequest();
        $strategy = new PaginationPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertNull($request->paginator());
    }

    public function testPagePaginatorIsCreatedForPageType(): void
    {
        $_GET['page'] = '2';
        $_GET['per_page'] = '10';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);
        $route->pagination(new Pagination(Pagination::TYPE_PAGE, 100));

        $request = $this->makeRequest();
        $strategy = new PaginationPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertInstanceOf(PagePaginator::class, $request->paginator());
    }

    public function testOffsetPaginatorIsCreatedForOffsetType(): void
    {
        $_GET['limit'] = '20';
        $_GET['offset'] = '40';

        $resource = new AnimalResource();
        $route = Route::resource($resource, AbstractResource::MODE_LIST);
        $route->pagination(new Pagination(Pagination::TYPE_OFFSET, 100));

        $request = $this->makeRequest();
        $strategy = new PaginationPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertInstanceOf(OffsetPaginator::class, $request->paginator());
    }

    private function makeRequest(): AbstractApivalkRequest
    {
        return new class extends AbstractApivalkRequest {
            /** @var mixed */
            private $paginator;

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }

            public function query(): ParameterBag
            {
                return new ParameterBag();
            }

            public function setPaginator($paginator): void
            {
                $this->paginator = $paginator;
            }

            public function paginator()
            {
                return $this->paginator;
            }
        };
    }
}
