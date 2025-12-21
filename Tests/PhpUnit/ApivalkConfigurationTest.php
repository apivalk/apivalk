<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\ApivalkConfiguration;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use apivalk\apivalk\Http\Renderer\JsonRenderer;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Middleware\MiddlewareStack;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ApivalkConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $router = $this->createMock(AbstractRouter::class);
        $config = new ApivalkConfiguration($router);

        $this->assertSame($router, $config->getRouter());
        $this->assertInstanceOf(JsonRenderer::class, $config->getRenderer());
        $this->assertInstanceOf(MiddlewareStack::class, $config->getMiddlewareStack());
        $this->assertNull($config->getExceptionHandler());
        $this->assertNull($config->getContainer());
        $this->assertInstanceOf(NullLogger::class, $config->getLogger());
    }

    public function testCustomConfiguration(): void
    {
        $router = $this->createMock(AbstractRouter::class);
        $renderer = $this->createMock(RendererInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $handler = function () {};

        $config = new ApivalkConfiguration($router, $renderer, $handler, $container, $logger);

        $this->assertSame($router, $config->getRouter());
        $this->assertSame($renderer, $config->getRenderer());
        $this->assertSame($handler, $config->getExceptionHandler());
        $this->assertSame($container, $config->getContainer());
        $this->assertSame($logger, $config->getLogger());
    }
}
