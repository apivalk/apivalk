<?php

declare(strict_types=1);

namespace apivalk\apivalk;

use apivalk\apivalk\Middleware\MiddlewareStack;
use apivalk\apivalk\Http\Renderer\JsonRenderer;
use apivalk\apivalk\Http\Renderer\RendererInterface;
use apivalk\apivalk\Router\AbstractRouter;
use Psr\Container\ContainerInterface;

//TODO: add optional "grantedscopeinterface" object
//TODO: add middleware which checks if current route scope/scopes are granted in the interface
class ApivalkConfiguration
{
    /** @var AbstractRouter */
    private $router;
    /** @var RendererInterface */
    private $renderer;
    /** @var MiddlewareStack */
    private $middlewareStack;
    /** @var callable|null */
    private $exceptionHandler;
    /** @var ContainerInterface|null */
    private $container;

    public function __construct(
        AbstractRouter $router,
        ?RendererInterface $renderer = null,
        ?callable $exceptionHandler = null,
        ?ContainerInterface $container = null
    ) {
        $this->router = $router;
        $this->middlewareStack = new MiddlewareStack();
        $this->renderer = $renderer ?? new JsonRenderer();
        $this->exceptionHandler = $exceptionHandler;
        $this->container = $container;
    }

    public function getMiddlewareStack(): MiddlewareStack
    {
        return $this->middlewareStack;
    }

    public function getRouter(): AbstractRouter
    {
        return $this->router;
    }

    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }

    public function getExceptionHandler(): ?callable
    {
        return $this->exceptionHandler;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }
}
