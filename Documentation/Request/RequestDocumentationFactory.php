<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\OperationGenerator;
use apivalk\apivalk\Http\Controller\Resource\AbstractCreateResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractDeleteResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractListResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractUpdateResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractViewResourceController;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Route;

final class RequestDocumentationFactory
{
    /**
     * Request documentation for a resource in a given mode — body properties only.
     * Path properties come from Route::getPathProperties() (declared explicitly in getRoute()).
     * Route-derived query properties (pagination, sorting, filtering) are intentionally excluded
     * so that OperationGenerator can add them without duplication when building OpenAPI specs.
     *
     * Use buildRuntimeDocumentation() when you need the fully merged documentation for request
     * population and validation.
     */
    public static function createRequestDocumentation(
        AbstractResource $resource,
        string $mode
    ): ApivalkRequestDocumentation {
        $documentation = new ApivalkRequestDocumentation();

        if ($mode === AbstractResource::MODE_CREATE || $mode === AbstractResource::MODE_UPDATE) {
            $excluded = $resource->excludeFromMode($mode);

            foreach ($resource->getProperties() as $property) {
                if (\in_array($property->getPropertyName(), $excluded, true)) {
                    continue;
                }

                if ($mode === AbstractResource::MODE_UPDATE) {
                    $property = clone $property;
                    $property->setIsRequired(false);
                }

                $documentation->addBodyProperty($property);
            }
        }

        return $documentation;
    }

    /**
     * Fully merged documentation for request population and validation at runtime. Combines base
     * request documentation, resource body properties, and route-derived path/query properties
     * (path params, pagination, sorting, filtering).
     *
     * @param Route  $route
     * @param string $controllerClass class-string<\apivalk\apivalk\Http\Controller\AbstractApivalkController>
     */
    public static function buildRuntimeDocumentation(
        Route $route,
        string $controllerClass
    ): ApivalkRequestDocumentation {
        /** @var class-string<\apivalk\apivalk\Http\Request\ApivalkRequestInterface> $requestClass */
        $requestClass = $controllerClass::getRequestClass();
        $documentation = $requestClass::getDocumentation();

        if (\is_subclass_of($controllerClass, AbstractResourceController::class)) {
            $resource = $controllerClass::getEmptyResource();
            $mode = self::getModeFromController($controllerClass);
            $resourceDocumentation = self::createRequestDocumentation($resource, $mode);

            foreach ($resourceDocumentation->getBodyProperties() as $property) {
                $documentation->addBodyProperty($property);
            }
        }

        foreach ($route->getPathProperties() as $property) {
            $documentation->addPathProperty($property);
        }

        foreach (OperationGenerator::getPaginationProperties($route) as $property) {
            $documentation->addQueryProperty($property);
        }

        $orderProperty = OperationGenerator::getOrderProperty($route);
        if ($orderProperty !== null) {
            $documentation->addQueryProperty($orderProperty);
        }

        foreach (OperationGenerator::getFilterProperties($route) as $property) {
            $documentation->addQueryProperty($property);
        }

        foreach ($route->getSortings() as $sorting) {
            $documentation->addAvailableSortField($sorting->getField());
        }

        return $documentation;
    }

    /**
     * @param class-string<AbstractResourceController<AbstractResource>> $controllerClass
     */
    public static function getModeFromController(string $controllerClass): string
    {
        if (\is_subclass_of($controllerClass, AbstractCreateResourceController::class)) {
            return AbstractResource::MODE_CREATE;
        }
        if (\is_subclass_of($controllerClass, AbstractUpdateResourceController::class)) {
            return AbstractResource::MODE_UPDATE;
        }
        if (\is_subclass_of($controllerClass, AbstractDeleteResourceController::class)) {
            return AbstractResource::MODE_DELETE;
        }
        if (\is_subclass_of($controllerClass, AbstractViewResourceController::class)) {
            return AbstractResource::MODE_VIEW;
        }
        if (\is_subclass_of($controllerClass, AbstractListResourceController::class)) {
            return AbstractResource::MODE_LIST;
        }

        return AbstractResource::MODE_VIEW;
    }
}
