<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\ApivalkConfiguration;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Util\ClassLocator;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use apivalk\apivalk\Middleware\MiddlewareStack;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ApivalkConfigurationTest extends TestCase
{
    public function testGetters(): void
    {
        $router = $this->createMock(AbstractRouter::class);
        $renderer = $this->createMock(RendererInterface::class);
        $exceptionHandler = function() {};
        $container = $this->createMock(ContainerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $config = new ApivalkConfiguration(
            $router,
            $renderer,
            $exceptionHandler,
            $container,
            $logger
        );

        $this->assertSame($router, $config->getRouter());
        $this->assertSame($renderer, $config->getRenderer());
        $this->assertSame($exceptionHandler, $config->getExceptionHandler());
        $this->assertSame($container, $config->getContainer());
        $this->assertSame($logger, $config->getLogger());
        $this->assertInstanceOf(MiddlewareStack::class, $config->getMiddlewareStack());
    }

    public function testDefaults(): void
    {
        $router = $this->createMock(AbstractRouter::class);

        $config = new ApivalkConfiguration($router);

        $this->assertInstanceOf(RendererInterface::class, $config->getRenderer());
        $this->assertNull($config->getExceptionHandler());
        $this->assertNull($config->getContainer());
        $this->assertInstanceOf(NullLogger::class, $config->getLogger());
    }
}
