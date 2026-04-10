<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;

class PagePaginatorTest extends TestCase
{
    public function testConstruct(): void
    {
        $paginator = new PagePaginator(2, 20);
        $this->assertEquals(2, $paginator->getPage());
        $this->assertEquals(20, $paginator->getLimit());
    }

    public function testInvalidValues(): void
    {
        $paginator = new PagePaginator(-1, 0);
        $this->assertEquals(0, $paginator->getPage());
        $this->assertEquals(50, $paginator->getLimit());
    }
}
