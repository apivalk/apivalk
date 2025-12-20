<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Paginator;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;

class PaginatorTest extends TestCase
{
    public function testPaginator(): void
    {
        $queryBag = new class extends ParameterBag {
            public $page = 2;
            public function __construct() {}
        };
        
        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('query')->willReturn($queryBag);

        $paginator = new Paginator($request, 20, 100);

        $this->assertEquals(2, $paginator->getPage());
        $this->assertEquals(20, $paginator->getPageSize());
        $this->assertEquals(5, $paginator->getTotalPages());
        $this->assertEquals(20, $paginator->getOffset());
    }

    public function testPaginatorFirstPage(): void
    {
        $queryBag = new class extends ParameterBag {
            public $page = 1;
            public function __construct() {}
        };
        
        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('query')->willReturn($queryBag);

        $paginator = new Paginator($request, 20, 100);

        $this->assertEquals(1, $paginator->getPage());
        $this->assertEquals(0, $paginator->getOffset());
    }

    public function testPaginatorNoTotal(): void
    {
        $request = $this->createMock(ApivalkRequestInterface::class);
        $paginator = new Paginator($request, 20, null);

        $this->assertNull($paginator->getTotalPages());
    }

    public function testInvalidPageSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = $this->createMock(ApivalkRequestInterface::class);
        new Paginator($request, 0, 100);
    }
}
