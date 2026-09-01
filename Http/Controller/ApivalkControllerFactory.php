<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller;

use Psr\Container\ContainerInterface;

class ApivalkControllerFactory implements ApivalkControllerFactoryInterface
{
    private ?ContainerInterface $container;

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

            self::assertInvokable($controller, $controllerClass);

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

        self::assertInvokable($controller, $controllerClass);

        return $controller;
    }

    /**
     * AbstractApivalkController cannot declare __invoke() abstractly without pinning every
     * controller to the interface parameter, so the contract is checked here as well as in
     * RouteCacheFactory. Without it a missing __invoke() surfaces as "object is not callable"
     * from inside the middleware stack.
     */
    private static function assertInvokable(AbstractApivalkController $controller, string $controllerClass): void
    {
        if (!\is_callable($controller)) {
            throw new \LogicException(\sprintf(
                'Controller "%s" has no __invoke() method.',
                $controllerClass
            ));
        }
    }
}
