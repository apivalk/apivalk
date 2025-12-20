<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller;

use Psr\Container\ContainerInterface;

class ApivalkControllerFactory implements ApivalkControllerFactoryInterface
{
    /** @var ContainerInterface|null */
    private $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function create(string $controllerClass): AbstractApivalkController
    {
        if ($this->container !== null && $this->container->has($controllerClass)) {
            $controller = $this->container->get($controllerClass);
            if (!$controller instanceof AbstractApivalkController) {
                throw new \InvalidArgumentException(
                    \sprintf('Controller "%s" must extend AbstractApivalkController', $controllerClass)
                );
            }

            return $controller;
        }

        if (!\class_exists($controllerClass)) {
            throw new \InvalidArgumentException(\sprintf('Controller class "%s" does not exist', $controllerClass));
        }

        $controller = new $controllerClass();
        if (!$controller instanceof AbstractApivalkController) {
            throw new \InvalidArgumentException(
                \sprintf('Controller "%s" must extend AbstractApivalkController', $controllerClass)
            );
        }

        return $controller;
    }
}
