<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Population\Strategy;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Pagination\PaginatorFactory;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Router\Route\Pagination\Pagination;

class PaginationPopulationStrategy implements PopulationStrategyInterface
{
    public function populate(AbstractApivalkRequest $request, RequestPopulationContext $context): void
    {
        $pagination = $context->getRoute()->getPagination();

        if ($pagination === null) {
            return;
        }

        switch ($pagination->getType()) {
            case Pagination::TYPE_OFFSET:
                $request->setPaginator(PaginatorFactory::offset($request, $pagination->getMaxLimit()));
                break;
            case Pagination::TYPE_CURSOR:
                $request->setPaginator(PaginatorFactory::cursor($request, $pagination->getMaxLimit()));
                break;
            case Pagination::TYPE_PAGE:
                $request->setPaginator(PaginatorFactory::page($request, $pagination->getMaxLimit()));
                break;
        }
    }
}
