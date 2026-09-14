<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;

/**
 * Reads the request class a controller expects from its own `__invoke()` signature, so a controller
 * does not have to name it a second time.
 */
final class RequestClassResolver
{
    /**
     * @param class-string<AbstractApivalkController> $controllerClass
     *
     * @return class-string<ApivalkRequestInterface>
     */
    public static function resolve(string $controllerClass): string
    {
        $parameters = (new \ReflectionMethod($controllerClass, '__invoke'))->getParameters();

        if ($parameters === []) {
            throw new \LogicException(\sprintf(
                '"%s::__invoke()" takes no parameter, so no request class can be derived from it. '
                . 'Declare it as __invoke(MyRequest $request).',
                $controllerClass
            ));
        }

        $type = $parameters[0]->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return ResourceRequest::class;
        }

        if ($type->isBuiltin()) {
            throw new \LogicException(\sprintf(
                '"%s::__invoke()" declares "%s" as its first parameter. It has to be a class '
                . 'implementing %s.',
                $controllerClass,
                $type->getName(),
                ApivalkRequestInterface::class
            ));
        }

        /** @var class-string $requestClass */
        $requestClass = $type->getName();

        if (!\is_a($requestClass, ApivalkRequestInterface::class, true)) {
            throw new \LogicException(\sprintf(
                '"%s::__invoke()" declares "%s" as its first parameter, which does not implement %s.',
                $controllerClass,
                $requestClass,
                ApivalkRequestInterface::class
            ));
        }

        // An interface or abstract parameter names nothing to instantiate. Everything such a
        // request carries then comes from the route, which is exactly what ResourceRequest is.
        if (!(new \ReflectionClass($requestClass))->isInstantiable()) {
            return ResourceRequest::class;
        }

        /** @var class-string<ApivalkRequestInterface> $requestClass */
        return $requestClass;
    }
}
