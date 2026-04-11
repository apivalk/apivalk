<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Sort;

use apivalk\apivalk\Router\Route\Sort\Sort;
use PHPUnit\Framework\TestCase;

class SortTest extends TestCase
{
    public function testAscFactory(): void
    {
        $sort = Sort::asc('status');

        $this->assertInstanceOf(Sort::class, $sort);
        $this->assertTrue($sort->isAsc());
        $this->assertFalse($sort->isDesc());
        $this->assertSame('status', $sort->getField());
    }

    public function testDescFactory(): void
    {
        $sort = Sort::desc('price');

        $this->assertInstanceOf(Sort::class, $sort);
        $this->assertFalse($sort->isAsc());
        $this->assertTrue($sort->isDesc());
        $this->assertSame('price', $sort->getField());
    }

    public function testConstructorDefaultsToAsc(): void
    {
        $sort = new Sort('id');

        $this->assertTrue($sort->isAsc());
        $this->assertFalse($sort->isDesc());
        $this->assertSame('id', $sort->getField());
    }

    public function testIsDescIsInverseOfIsAsc(): void
    {
        $ascSort = Sort::asc('foo');
        $descSort = Sort::desc('bar');

        $this->assertSame(!$ascSort->isAsc(), $ascSort->isDesc());
        $this->assertSame(!$descSort->isAsc(), $descSort->isDesc());
    }
}
