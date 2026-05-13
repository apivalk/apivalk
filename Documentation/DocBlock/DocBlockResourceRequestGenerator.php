<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Documentation\Request\RequestDocumentationFactory;
use apivalk\apivalk\Http\Controller\Resource\AbstractListResourceController;
use apivalk\apivalk\Http\Request\Pagination\CursorPaginator;
use apivalk\apivalk\Http\Request\Pagination\OffsetPaginator;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\Sort;

final class DocBlockResourceRequestGenerator
{
    /**
     * @param class-string<\apivalk\apivalk\Http\Controller\Resource\AbstractResourceController<AbstractResource>> $controllerClass
     */
    public function generate(string $controllerClass, Route $route): DocBlockResourceRequest
    {
        $resource = $controllerClass::getEmptyResource();
        $mode = RequestDocumentationFactory::getModeFromController($controllerClass);
        $requestName = \ucfirst($resource->getName()) . \ucfirst($mode);

        $pathShape = new DocBlockShape($requestName, 'Path');
        $sortingShape = new DocBlockShape($requestName, 'Sorting');
        $filteringShape = new DocBlockShape($requestName, 'Filtering');

        foreach ($route->getPathProperties() as $property) {
            $pathShape->addProperty($property);
        }

        if (\is_subclass_of($controllerClass, AbstractListResourceController::class)) {
            foreach ($resource->availableSortings() as $sorting) {
                $sortingShape->addCustomField($sorting->getField(), '\\' . Sort::class);
            }

            /** @var FilterInterface $filter */
            foreach ($resource->availableFilters() as $filter) {
                $filteringShape->addCustomField($filter->getField(), '\\' . \get_class($filter));
            }
        }

        $paginatorClass = null;
        $pagination = $route->getPagination();

        if ($pagination !== null) {
            switch ($pagination->getType()) {
                case Pagination::TYPE_PAGE:
                    $paginatorClass = PagePaginator::class;
                    break;
                case Pagination::TYPE_OFFSET:
                    $paginatorClass = OffsetPaginator::class;
                    break;
                case Pagination::TYPE_CURSOR:
                    $paginatorClass = CursorPaginator::class;
                    break;
            }
        }

        return new DocBlockResourceRequest(
            $pathShape,
            $sortingShape,
            $filteringShape,
            $paginatorClass,
            $controllerClass::getRequestClass()
        );
    }
}
