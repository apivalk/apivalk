<?php

declare(strict_types=1);

namespace apivalk\apivalk;

use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use apivalk\apivalk\Router\AbstractRouter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Apivalk
{
    /** @var ApivalkConfiguration */
    private $configuration;

    public function __construct(ApivalkConfiguration $apivalkConfiguration)
    {
        $this->configuration = $apivalkConfiguration;

        if ($apivalkConfiguration->getExceptionHandler() !== null) {
            set_exception_handler($apivalkConfiguration->getExceptionHandler());
        }
    }

    public function run(): AbstractApivalkResponse
    {
        return $this->configuration->getRouter()->dispatch($this->configuration->getMiddlewareStack());
    }

    public function getMiddlewareStack(): MiddlewareStack
    {
        return $this->configuration->getMiddlewareStack();
    }

    public function getRenderer(): RendererInterface
    {
        return $this->configuration->getRenderer();
    }

    public function getRouter(): AbstractRouter
    {
        return $this->configuration->getRouter();
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->configuration->getContainer();
    }

    public function getLogger(): LoggerInterface
    {
        return $this->configuration->getLogger();
    }
}
