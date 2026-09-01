<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Population\Strategy;

use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\Operator;
use apivalk\apivalk\Router\Route\Route;

class FilteringPopulationStrategy implements PopulationStrategyInterface
{
    public function populate(AbstractApivalkRequest $request, RequestPopulationContext $context): void
    {
        $filterBag = new FilterBag();
        $route = $context->getRoute();
        $body = $this->readQueryBody($route);

        foreach ($route->getFilters() as $filter) {
            $field = $filter->getField();
            $clonedFilter = clone $filter;

            if ($body !== null && isset($_GET[$field])) {
                // Both transports on one request is a contradiction, not something to merge.
                $filterBag->addViolation($field, '');
                $filterBag->set($clonedFilter);

                continue;
            }

            $supplied = $body !== null ? ($body[$field] ?? null) : ($_GET[$field] ?? null);

            if (\is_array($supplied)) {
                $this->populateFromOperatorMap($clonedFilter, $filterBag, $supplied);
            } elseif (\is_scalar($supplied)) {
                $this->setCondition($clonedFilter, $clonedFilter->getDefaultOperator(), $supplied);
            }

            $filterBag->set($clonedFilter);
        }

        $request->setFilterBag($filterBag);
    }

    /**
     * The JSON body of an RFC 10008 QUERY request, or null when this is not one.
     *
     * @return array<string, mixed>|null
     */
    private function readQueryBody(Route $route): ?array
    {
        if (!$route->isQueryEnabled()) {
            return null;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== MethodInterface::METHOD_QUERY) {
            return null;
        }

        $body = json_decode((string)file_get_contents('php://input'), true);
        $body = \is_array($body) ? $body : [];

        // Same overlay ParameterBagFactory::createBodyBag() applies, so a form-encoded
        // body reaches filters the way a JSON one does.
        foreach ($_POST as $key => $value) {
            $body[$key] = $value;
        }

        return $body;
    }

    /**
     * @param array<mixed, mixed> $supplied
     */
    private function populateFromOperatorMap(
        FilterInterface $filter,
        FilterBag $filterBag,
        array $supplied
    ): void {
        foreach ($supplied as $operator => $value) {
            if (!\is_string($operator) || !$filter->allows($operator)) {
                $filterBag->addViolation($filter->getField(), (string)$operator);

                continue;
            }

            if (!\is_scalar($value) && !($operator === Operator::IN && \is_array($value))) {
                $filterBag->addViolation($filter->getField(), $operator);

                continue;
            }

            $this->setCondition($filter, $operator, $value);
        }
    }

    /**
     * @param scalar|array<mixed, mixed> $rawValue
     */
    private function setCondition(FilterInterface $filter, string $operator, $rawValue): void
    {
        $filter->setCondition(
            $operator,
            $this->castValue($filter, $operator, $rawValue),
            \is_scalar($rawValue) ? (string)$rawValue : null
        );
    }

    /**
     * @param scalar|array<mixed, mixed> $rawValue
     *
     * @return mixed
     */
    private function castValue(FilterInterface $filter, string $operator, $rawValue)
    {
        if ($operator === Operator::NULL) {
            return \is_bool($rawValue)
                ? $rawValue
                : filter_var((string)$rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $property = $filter->getProperty();

        if ($operator === Operator::IN) {
            $items = \is_array($rawValue)
                ? $rawValue
                : array_map('trim', explode(',', (string)$rawValue));

            return array_map(
                static fn($item) => ParameterBagFactory::typeCastValueByProperty($item, $property),
                array_values($items)
            );
        }

        return ParameterBagFactory::typeCastValueByProperty($rawValue, $property);
    }
}
