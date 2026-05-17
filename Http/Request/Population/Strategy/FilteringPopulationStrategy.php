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
        $bracketValues = isset($_GET['filter']) && \is_array($_GET['filter']) ? $_GET['filter'] : [];

        foreach ($context->getRoute()->getFilters() as $filter) {
            $field = $filter->getField();
            $clonedFilter = clone $filter;
            $queryParameter = $request->query()->get($field);

            if ($queryParameter !== null) {
                // Flat notation: ?status=active
                $rawValue = $queryParameter->getRawValue();
                $clonedFilter->setRawValue(\is_scalar($rawValue) ? (string) $rawValue : null);
                $clonedFilter->setValue(
                    ParameterBagFactory::typeCastValueByProperty($rawValue, $filter->getProperty())
                );
            } elseif (array_key_exists($field, $bracketValues) && \is_scalar($bracketValues[$field])) {
                // Bracket notation: ?filter[status]=active
                $rawValue = (string) $bracketValues[$field];
                $clonedFilter->setRawValue($rawValue);
                $clonedFilter->setValue(
                    ParameterBagFactory::typeCastValueByProperty($rawValue, $filter->getProperty())
                );
            }

            $filterBag->set($clonedFilter);
        }

        $request->setFilterBag($filterBag);
    }
}
