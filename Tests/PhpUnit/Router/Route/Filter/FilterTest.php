<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testInterfaceConstants(): void
    {
        $this->assertSame('equals', FilterInterface::TYPE_EQUALS);
        $this->assertSame('in', FilterInterface::TYPE_IN);
        $this->assertSame('like', FilterInterface::TYPE_LIKE);
        $this->assertSame('greater_than', FilterInterface::TYPE_GREATER_THAN);
        $this->assertSame('less_than', FilterInterface::TYPE_LESS_THAN);
        $this->assertSame('contains', FilterInterface::TYPE_CONTAINS);
    }
}
