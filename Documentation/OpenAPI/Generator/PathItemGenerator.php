<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\OpenAPI\Object\OperationObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathItemObject;
use apivalk\apivalk\Documentation\Request\RequestDocumentationFactory;
use apivalk\apivalk\Documentation\Response\ResponseDocumentationFactory;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Controller\Resource\AbstractDeleteResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractResourceController;
use apivalk\apivalk\Http\Method\DeleteMethod;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Method\PatchMethod;
use apivalk\apivalk\Http\Method\PostMethod;
use apivalk\apivalk\Http\Method\PutMethod;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Route;

class PathItemGenerator
{
    /** @var bool */
    private $documentLocaleHeaders;

    public function __construct(bool $documentLocaleHeaders = true)
    {
        $this->documentLocaleHeaders = $documentLocaleHeaders;
    }

    /**
     * @param array<int, array{controllerClass: string, route: Route}> $routes All routes with controller class name that have the same URL pattern but different methods.
     *
     * @return PathItemObject
     */
    public function generate(array $routes): PathItemObject
    {
        $operationGenerator = new OperationGenerator($this->documentLocaleHeaders);

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

            $operation = $this->generateOperation($operationGenerator, $route, $controllerClass);

            if ($route->getMethod() instanceof GetMethod) {
                $get = $operation;
            }

            if ($route->getMethod() instanceof PatchMethod) {
                $patch = $operation;
            }

            if ($route->getMethod() instanceof DeleteMethod) {
                $delete = $operation;
            }

            if ($route->getMethod() instanceof PostMethod) {
                $post = $operation;
            }

            if ($route->getMethod() instanceof PutMethod) {
                $put = $operation;
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

    /**
     * @param OperationGenerator                      $operationGenerator
     * @param Route                                   $route
     * @param class-string<AbstractApivalkController> $controllerClass
     *
     * @return OperationObject
     */
    private function generateOperation(
        OperationGenerator $operationGenerator,
        Route $route,
        string $controllerClass
    ): OperationObject {
        if (\is_subclass_of($controllerClass, AbstractResourceController::class)) {
            return $this->generateResourceOperation($operationGenerator, $route, $controllerClass);
        }

        /** @var class-string<ApivalkRequestInterface> $requestClass */
        $requestClass = $controllerClass::getRequestClass();
        $responseClasses = $controllerClass::getResponseClasses();

        return $operationGenerator->generate(
            $route,
            $requestClass::getDocumentation(),
            $responseClasses
        );
    }

    /**
     * @param OperationGenerator                       $operationGenerator
     * @param Route                                    $route
     * @param class-string<AbstractResourceController<AbstractResource>> $controllerClass
     *
     * @return OperationObject
     */
    private function generateResourceOperation(
        OperationGenerator $operationGenerator,
        Route $route,
        string $controllerClass
    ): OperationObject {
        $resource = $controllerClass::getEmptyResource();
        $mode = $controllerClass::getMode();

        $requestDocumentation = RequestDocumentationFactory::createRequestDocumentation($resource, $mode);
        $responseDocumentation = ResponseDocumentationFactory::create($resource, $mode);

        $responseDocumentations = [];

        $isDeleteMode = \is_subclass_of($controllerClass, AbstractDeleteResourceController::class);

        foreach ($controllerClass::getResponseClasses() as $responseClass) {
            $statusCode = (int)$responseClass::getStatusCode();
            $isSuccessResponse = $statusCode >= 200 && $statusCode < 300;

            // For non-delete success responses, use resource-derived documentation.
            // Delete success responses and all error responses use the class-declared documentation.
            $useResourceDoc = $isSuccessResponse && !$isDeleteMode;

            $responseDocumentations[] = [
                'statusCode' => $statusCode,
                'documentation' => $useResourceDoc ? $responseDocumentation : $responseClass::getDocumentation(),
            ];
        }

        return $operationGenerator->generateFromDocumentation(
            $route,
            $requestDocumentation,
            $responseDocumentations
        );
    }
}
