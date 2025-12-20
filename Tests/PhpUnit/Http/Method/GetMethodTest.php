<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Method;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Method\GetMethod;

class GetMethodTest extends TestCase
{
    public function testGetName(): void
    {
        $method = new GetMethod();
        $this->assertEquals('GET', $method->getName());
    }
}
