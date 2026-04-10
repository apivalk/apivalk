<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Router\Route\Pagination\Pagination;

class PaginationTest extends TestCase
{
    public function testCursor(): void
    {
        $pagination = Pagination::cursor();
        $this->assertEquals(Pagination::TYPE_CURSOR, $pagination->getType());
    }

    public function testOffset(): void
    {
        $pagination = Pagination::offset();
        $this->assertEquals(Pagination::TYPE_OFFSET, $pagination->getType());
    }

    public function testPage(): void
    {
        $pagination = Pagination::page();
        $this->assertEquals(Pagination::TYPE_PAGE, $pagination->getType());
    }

    public function testMaxLimit(): void
    {
        $pagination = Pagination::page();
        $this->assertEquals(100, $pagination->getMaxLimit());
        
        $pagination->setMaxLimit(500);
        $this->assertEquals(500, $pagination->getMaxLimit());
    }
}
