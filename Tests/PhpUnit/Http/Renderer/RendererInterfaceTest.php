<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Renderer;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Renderer\RendererInterface;

class RendererInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(RendererInterface::class));
    }
}
