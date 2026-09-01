<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\File\File;
use apivalk\apivalk\Http\Request\Pagination\CursorPaginator;
use apivalk\apivalk\Http\Request\Pagination\OffsetPaginator;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;

final class DocBlockRequestGenerator
{
    public function generate(AbstractApivalkRequest $abstractApivalkRequest, Route $route): DocBlockRequest
    {
        $documentation = $abstractApivalkRequest::getDocumentation();

        $requestName = (new \ReflectionClass($abstractApivalkRequest))->getShortName();

        $bodyShape = new DocBlockShape($requestName, 'Body');
        $pathShape = new DocBlockShape($requestName, 'Path');
        $queryShape = new DocBlockShape($requestName, 'Query');
        $sortingShape = new DocBlockShape($requestName, 'Sorting');
        $filteringShape = new DocBlockShape($requestName, 'Filtering');
        $fileShape = new DocBlockShape($requestName, 'File');

        foreach ($documentation->getBodyProperties() as $property) {
            $bodyShape->addProperty($property);
        }

        foreach ($documentation->getPathProperties() as $property) {
            $pathShape->addProperty($property);
        }

        foreach ($route->getPathProperties() as $property) {
            $pathShape->addProperty($property);
        }

        foreach ($documentation->getQueryProperties() as $property) {
            $queryShape->addProperty($property);
        }

        // The file bag holds File objects, not the property's PHP type, so the type is declared explicitly.
        foreach ($documentation->getFileProperties() as $property) {
            $fileShape->addCustomField(
                $property->getPropertyName(),
                $property->isRequired() ? '\\' . File::class : '\\' . File::class . '|null'
            );
        }

        foreach ($route->getSortings() as $ordering) {
            $sortingShape->addCustomField($ordering->getField(), '\\' . Sort::class);
        }

        $filterFieldShapes = [];
        foreach ($route->getFilters() as $filter) {
            $fieldShape = FilterShapeFactory::build($requestName, $filter);
            $filterFieldShapes[] = $fieldShape;
            $filteringShape->addCustomField($filter->getField(), $fieldShape->getClassName());
        }

        if ($filterFieldShapes !== []) {
            FilterShapeFactory::decorateBagShape($filteringShape);
        }

        $paginatorClass = null;
        if ($route->getPagination() !== null) {
            switch ($route->getPagination()->getType()) {
                case Pagination::TYPE_CURSOR:
                    $paginatorClass = CursorPaginator::class;
                    break;
                case Pagination::TYPE_OFFSET:
                    $paginatorClass = OffsetPaginator::class;
                    break;
                case Pagination::TYPE_PAGE:
                    $paginatorClass = PagePaginator::class;
                    break;
            }
        }

        $docBlockRequest = new DocBlockRequest(
            $bodyShape,
            $pathShape,
            $queryShape,
            $sortingShape,
            $filteringShape,
            $paginatorClass,
            $fileShape
        );
        $docBlockRequest->setFilterFieldShapes($filterFieldShapes);

        return $docBlockRequest;
    }
}
