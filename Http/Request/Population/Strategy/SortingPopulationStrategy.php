<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Population\Strategy;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Router\Route\Sort\SortBag;

class SortingPopulationStrategy implements PopulationStrategyInterface
{
    public function populate(AbstractApivalkRequest $request, RequestPopulationContext $context): void
    {
        $sortBag = new SortBag();

        $rawOrderBy = isset($_GET['order_by']) ? (string)$_GET['order_by'] : null;

        if ($rawOrderBy !== null && $rawOrderBy !== '') {
            foreach (explode(',', $rawOrderBy) as $curOrderByField) {
                $curOrderByField = trim($curOrderByField);
                if ($curOrderByField === '') {
                    continue;
                }

                if ($curOrderByField[0] !== '+' && $curOrderByField[0] !== '-') {
                    $direction = '+';
                    $field = $curOrderByField;
                } else {
                    $direction = $curOrderByField[0];
                    $field = substr($curOrderByField, 1);
                }

                if ($field === '') {
                    continue;
                }

                $sort = $direction === '-' ? Sort::desc($field) : Sort::asc($field);
                $sort->markAsRequested();

                $sortBag->set($sort);
            }
        }

        foreach ($context->getRoute()->getSortings() as $sort) {
            if (!$sortBag->has($sort->getField())) {
                $sortBag->set($sort);
            }
        }

        $request->setSortBag($sortBag);
    }
}
