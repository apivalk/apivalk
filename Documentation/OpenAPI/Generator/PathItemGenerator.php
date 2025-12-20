<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathItemObject;
use apivalk\apivalk\Http\Method\DeleteMethod;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Method\PatchMethod;
use apivalk\apivalk\Http\Method\PostMethod;
use apivalk\apivalk\Http\Method\PutMethod;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;

class PathItemGenerator
{
    /**
     * @param array<string, array> $routes All routes with controller class name that have the same URL pattern but different methods. Example: ['controllerClass' => 'CreateController', 'route' => $createControllerRoute]
     *
     * @return PathItemObject
     */
    public function generate(array $routes): PathItemObject
    {
        $operationGenerator = new OperationGenerator();

        $get = null;
        $put = null;
        $post = null;
        $delete = null;
        $options = null;
        $head = null;
        $patch = null;
        $trace = null;

        foreach ($routes as $routeContainer) {
            $route = $routeContainer['route'];
            /** @var class-string<AbstractApivalkController> $controllerClass */
            $controllerClass = $routeContainer['controllerClass'];

            /** @var class-string<ApivalkRequestInterface> $request */
            $request = $controllerClass::getRequestClass();
            $responseClasses = $controllerClass::getResponseClasses();

            if ($route->getMethod() instanceof GetMethod) {
                $get = $operationGenerator->generate(
                    $route,
                    $request::getDocumentation(),
                    $responseClasses
                );
            }

            if ($route->getMethod() instanceof PatchMethod) {
                $patch = $operationGenerator->generate(
                    $route,
                    $request::getDocumentation(),
                    $responseClasses
                );
            }

            if ($route->getMethod() instanceof DeleteMethod) {
                $delete = $operationGenerator->generate(
                    $route,
                    $request::getDocumentation(),
                    $responseClasses
                );
            }

            if ($route->getMethod() instanceof PostMethod) {
                $post = $operationGenerator->generate(
                    $route,
                    $request::getDocumentation(),
                    $responseClasses
                );
            }

            if ($route->getMethod() instanceof PutMethod) {
                $put = $operationGenerator->generate(
                    $route,
                    $request::getDocumentation(),
                    $responseClasses
                );
            }
        }

        return new PathItemObject(
            null,
            null,
            $get,
            $put,
            $post,
            $delete,
            $options,
            $head,
            $patch,
            $trace,
            []
        );
    }
}
