<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Http\Controller\Resource\AbstractListResourceController;
use apivalk\apivalk\Http\Request\Pagination\CursorPaginator;
use apivalk\apivalk\Http\Request\Pagination\OffsetPaginator;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Sort\Sort;

final class DocBlockResourceRequestGenerator
{
    /**
     * @param class-string<AbstractListResourceController<AbstractResource>> $controllerClass
     */
    public function generate(string $controllerClass): DocBlockResourceRequest
    {
        $resource = $controllerClass::getEmptyResource();
        $requestName = \ucfirst($resource->getName()) . \ucfirst($controllerClass::getMode());

        $sortingShape = new DocBlockShape($requestName, 'Sorting');
        $filteringShape = new DocBlockShape($requestName, 'Filtering');

        foreach ($resource->availableSortings() as $sorting) {
            $sortingShape->addCustomField($sorting->getField(), '\\' . Sort::class);
        }

        /** @var FilterInterface $filter */
        foreach ($resource->availableFilters() as $filter) {
            $filteringShape->addCustomField($filter->getField(), '\\' . \get_class($filter));
        }

        $paginatorClass = null;
        $pagination = $controllerClass::pagination();

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
            $sortingShape,
            $filteringShape,
            $paginatorClass,
            $controllerClass::getRequestClass()
        );
    }
}
