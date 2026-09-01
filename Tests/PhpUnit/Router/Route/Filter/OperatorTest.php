<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use apivalk\apivalk\Router\Route\Filter\Operator;
use PHPUnit\Framework\TestCase;

class OperatorTest extends TestCase
{
    /**
     * The constant values travel on the wire, so changing one breaks every client.
     */
    public function testWireNames(): void
    {
        $this->assertSame('eq', Operator::EQ);
        $this->assertSame('neq', Operator::NEQ);
        $this->assertSame('in', Operator::IN);
        $this->assertSame('gt', Operator::GT);
        $this->assertSame('gte', Operator::GTE);
        $this->assertSame('lt', Operator::LT);
        $this->assertSame('lte', Operator::LTE);
        $this->assertSame('like', Operator::LIKE);
        $this->assertSame('contains', Operator::CONTAINS);
        $this->assertSame('null', Operator::NULL);
    }
}
