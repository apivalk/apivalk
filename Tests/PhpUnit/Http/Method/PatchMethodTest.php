<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Method;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Method\PatchMethod;

class PatchMethodTest extends TestCase
{
    public function testGetName(): void
    {
        $method = new PatchMethod();
        $this->assertEquals('PATCH', $method->getName());
    }
}
