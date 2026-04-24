<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Population\Strategy;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Router\Route\Filter\FilterBag;

class FilteringPopulationStrategy implements PopulationStrategyInterface
{
    public function populate(AbstractApivalkRequest $request, RequestPopulationContext $context): void
    {
        $filterBag = new FilterBag();

        foreach ($context->getRoute()->getFilters() as $filter) {
            $queryParameter = $request->query()->get($filter->getField());

            $clonedFilter = clone $filter;
            if ($queryParameter !== null) {
                $clonedFilter->setValue(
                    ParameterBagFactory::typeCastValueByProperty(
                        $queryParameter->getRawValue(),
                        $filter->getProperty()
                    )
                );
            }
            $filterBag->set($clonedFilter);
        }

        $request->setFilterBag($filterBag);
    }
}
