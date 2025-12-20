<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;

class ApivalkRequestInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(ApivalkRequestInterface::class));
    }
}
