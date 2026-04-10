<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Pagination\OffsetPaginator;

class OffsetPaginatorTest extends TestCase
{
    public function testConstruct(): void
    {
        $paginator = new OffsetPaginator(40, 20);
        $this->assertEquals(40, $paginator->getOffset());
        $this->assertEquals(20, $paginator->getLimit());
    }

    public function testInvalidValues(): void
    {
        $paginator = new OffsetPaginator(-1, 0);
        $this->assertEquals(0, $paginator->getOffset());
        $this->assertEquals(50, $paginator->getLimit());
    }
}
