<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Method;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Method\MethodInterface;

class MethodInterfaceTest extends TestCase
{
    public function testConstants(): void
    {
        $this->assertEquals('GET', MethodInterface::METHOD_GET);
        $this->assertEquals('POST', MethodInterface::METHOD_POST);
        $this->assertEquals('DELETE', MethodInterface::METHOD_DELETE);
        $this->assertEquals('PUT', MethodInterface::METHOD_PUT);
        $this->assertEquals('PATCH', MethodInterface::METHOD_PATCH);
    }
}
