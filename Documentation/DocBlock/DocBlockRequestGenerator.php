<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Pagination\CursorPaginator;
use apivalk\apivalk\Http\Request\Pagination\OffsetPaginator;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Router\Route\Order\Order;
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
        $orderingShape = new DocBlockShape($requestName, 'Ordering');

        foreach ($documentation->getBodyProperties() as $property) {
            $bodyShape->addProperty($property);
        }

        foreach ($documentation->getPathProperties() as $property) {
            $pathShape->addProperty($property);
        }

        foreach ($documentation->getQueryProperties() as $property) {
            $queryShape->addProperty($property);
        }

        foreach ($route->getOrderings() as $ordering) {
            $orderingShape->addCustomField($ordering->getField(), '\\' . Order::class);
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

        return new DocBlockRequest(
            $bodyShape,
            $pathShape,
            $queryShape,
            $orderingShape,
            $paginatorClass
        );
    }
}
