<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Method;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Method\PutMethod;

class PutMethodTest extends TestCase
{
    public function testGetName(): void
    {
        $method = new PutMethod();
        $this->assertEquals('PUT', $method->getName());
    }
}
