<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\ResponsePagination;
use apivalk\apivalk\Http\Request\Paginator;

class ResponsePaginationTest extends TestCase
{
    public function testToArray(): void
    {
        $pagination = new ResponsePagination(1, 10, 20);

        $this->assertEquals(1, $pagination->getPage());
        $this->assertEquals(10, $pagination->getTotalPages());
        $this->assertEquals(20, $pagination->getPageSize());
        
        $expected = [
            'page' => 1,
            'total_pages' => 10,
            'page_size' => 20
        ];
        $this->assertEquals($expected, $pagination->toArray());
    }

    public function testCreateByPaginator(): void
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getPage')->willReturn(2);
        $paginator->method('getTotalPages')->willReturn(5);
        $paginator->method('getPageSize')->willReturn(50);

        $pagination = ResponsePagination::createByPaginator($paginator);
        
        $this->assertEquals(2, $pagination->getPage());
        $this->assertEquals(5, $pagination->getTotalPages());
        $this->assertEquals(50, $pagination->getPageSize());
    }
}
