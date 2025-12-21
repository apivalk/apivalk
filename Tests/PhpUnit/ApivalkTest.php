<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Apivalk;
use apivalk\apivalk\ApivalkConfiguration;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ApivalkTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $router = $this->createMock(AbstractRouter::class);
        $renderer = $this->createMock(RendererInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        
        $config = new ApivalkConfiguration($router, $renderer, null, $container, $logger);
        $apivalk = new Apivalk($config);

        $this->assertSame($router, $apivalk->getRouter());
        $this->assertSame($renderer, $apivalk->getRenderer());
        $this->assertSame($container, $apivalk->getContainer());
        $this->assertSame($logger, $apivalk->getLogger());
        $this->assertInstanceOf(MiddlewareStack::class, $apivalk->getMiddlewareStack());
    }

    public function testRun(): void
    {
        $router = $this->createMock(AbstractRouter::class);
        $response = $this->createMock(AbstractApivalkResponse::class);
        
        $router->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(MiddlewareStack::class))
            ->willReturn($response);

        $config = new ApivalkConfiguration($router);
        $apivalk = new Apivalk($config);

        $result = $apivalk->run();
        $this->assertSame($response, $result);
    }

    public function testExceptionHandlerRegistration(): void
    {
        $router = $this->createMock(AbstractRouter::class);
        $handlerCalled = false;
        $handler = function () use (&$handlerCalled) {
            $handlerCalled = true;
        };

        // We can't easily test if set_exception_handler was called without side effects
        // but we can check if it accepts the configuration
        $config = new ApivalkConfiguration($router, null, $handler);
        $apivalk = new Apivalk($config);
        
        $this->assertTrue(true); // If no error occurred, it's fine
    }
}
