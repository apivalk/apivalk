<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Pagination\PaginatorFactory;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class PaginatorFactoryTest extends TestCase
{
    private function createRequestWithQuery(array $queryData): AbstractApivalkRequest
    {
        $queryBag = new ParameterBag();
        foreach ($queryData as $key => $value) {
            $queryBag->set(new Parameter($key, $value, $value));
        }

        $request = $this->getMockBuilder(AbstractApivalkRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['query'])
            ->getMockForAbstractClass();

        $request->method('query')->willReturn($queryBag);

        return $request;
    }

    public function testOffset(): void
    {
        $request = $this->createRequestWithQuery(['limit' => 20, 'offset' => 40]);
        $paginator = PaginatorFactory::offset($request);

        $this->assertEquals(20, $paginator->getLimit());
        $this->assertEquals(40, $paginator->getOffset());
    }

    public function testOffsetDefaults(): void
    {
        $request = $this->createRequestWithQuery([]);
        $paginator = PaginatorFactory::offset($request);

        $this->assertEquals(50, $paginator->getLimit());
        $this->assertEquals(0, $paginator->getOffset());
    }

    public function testPage(): void
    {
        $request = $this->createRequestWithQuery(['limit' => 10, 'page' => 3]);
        $paginator = PaginatorFactory::page($request);

        $this->assertEquals(10, $paginator->getLimit());
        $this->assertEquals(3, $paginator->getPage());
    }

    public function testPageDefaults(): void
    {
        $request = $this->createRequestWithQuery([]);
        $paginator = PaginatorFactory::page($request);

        $this->assertEquals(50, $paginator->getLimit());
        $this->assertEquals(1, $paginator->getPage());
    }

    public function testCursor(): void
    {
        $request = $this->createRequestWithQuery(['limit' => 15, 'cursor' => 'abc']);
        $paginator = PaginatorFactory::cursor($request);

        $this->assertEquals(15, $paginator->getLimit());
        $this->assertEquals('abc', $paginator->getCursor());
    }

    public function testCursorDefaults(): void
    {
        $request = $this->createRequestWithQuery([]);
        $paginator = PaginatorFactory::cursor($request);

        $this->assertEquals(50, $paginator->getLimit());
        $this->assertNull($paginator->getCursor());
    }

    public function testResolveLimitMaxLimit(): void
    {
        $request = $this->createRequestWithQuery(['limit' => 200]);
        $paginator = PaginatorFactory::page($request, 100);

        $this->assertEquals(100, $paginator->getLimit());
    }
}
