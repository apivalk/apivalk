<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Apivalk;
use apivalk\apivalk\ApivalkConfiguration;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ApivalkTest extends TestCase
{
    public function testGetters(): void
    {
        $middlewareStack = new MiddlewareStack();
        $renderer = $this->createMock(RendererInterface::class);
        $router = $this->createMock(AbstractRouter::class);
        $container = $this->createMock(ContainerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $config = $this->createMock(ApivalkConfiguration::class);
        $config->method('getMiddlewareStack')->willReturn($middlewareStack);
        $config->method('getRenderer')->willReturn($renderer);
        $config->method('getRouter')->willReturn($router);
        $config->method('getContainer')->willReturn($container);
        $config->method('getLogger')->willReturn($logger);

        $apivalk = new Apivalk($config);

        $this->assertSame($middlewareStack, $apivalk->getMiddlewareStack());
        $this->assertSame($renderer, $apivalk->getRenderer());
        $this->assertSame($router, $apivalk->getRouter());
        $this->assertSame($container, $apivalk->getContainer());
        $this->assertSame($logger, $apivalk->getLogger());
    }

    public function testRun(): void
    {
        $middlewareStack = new MiddlewareStack();
        $router = $this->createMock(AbstractRouter::class);
        $response = $this->createMock(AbstractApivalkResponse::class);

        $router->expects($this->once())
            ->method('dispatch')
            ->with($middlewareStack)
            ->willReturn($response);

        $config = $this->createMock(ApivalkConfiguration::class);
        $config->method('getMiddlewareStack')->willReturn($middlewareStack);
        $config->method('getRouter')->willReturn($router);

        $apivalk = new Apivalk($config);
        $result = $apivalk->run();

        $this->assertSame($response, $result);
    }
}
