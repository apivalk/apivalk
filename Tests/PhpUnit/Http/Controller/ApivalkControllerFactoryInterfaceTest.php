<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Controller;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactoryInterface;

class ApivalkControllerFactoryInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(ApivalkControllerFactoryInterface::class));
    }
}
