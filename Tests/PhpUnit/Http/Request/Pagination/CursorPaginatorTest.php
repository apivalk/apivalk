<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Pagination\CursorPaginator;

class CursorPaginatorTest extends TestCase
{
    public function testConstruct(): void
    {
        $paginator = new CursorPaginator('abc', 20);
        $this->assertEquals('abc', $paginator->getCursor());
        $this->assertEquals(20, $paginator->getLimit());
    }

    public function testInvalidValues(): void
    {
        $paginator = new CursorPaginator(null, 0);
        $this->assertNull($paginator->getCursor());
        $this->assertEquals(50, $paginator->getLimit());
    }
}
