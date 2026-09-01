<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\FileProperty;
use apivalk\apivalk\Documentation\Property\Validator\FileValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Response\ValidationErrorObject;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\Parameter\Parameter;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\Operator;
use apivalk\apivalk\Router\Route\Sort\SortBag;

class RequestValidationMiddleware implements MiddlewareInterface
{
    /**
     * Ordered operator pairs where the left bound must not exceed the right one.
     *
     * @var array<int, string[]>
     */
    private const RANGE_PAIRS = [
        [Operator::GT, Operator::LT],
        [Operator::GT, Operator::LTE],
        [Operator::GTE, Operator::LT],
        [Operator::GTE, Operator::LTE],
    ];

    /** @var ValidationErrorObject[] */
    private array $errors = [];

    public function process(
        ApivalkRequestInterface $request,
        AbstractApivalkController $controller,
        callable $next
    ): AbstractApivalkResponse {
        $this->errors = [];

        $documentation = $request->getRuntimeDocumentation();

        $this->validateProperties($documentation->getBodyProperties(), $request->body());
        $this->validateProperties($documentation->getQueryProperties(), $request->query());
        $this->validateProperties($documentation->getPathProperties(), $request->path());
        $this->validateFiles($documentation->getFileProperties(), $request->file());
        $this->validateFilters($request->filtering());
        $this->validateSortings($request->sorting(), $documentation->getAvailableSortFields());

        if (\count($this->errors) > 0) {
            return new BadValidationApivalkResponse($this->errors);
        }

        return $next($request);
    }

    /**
     * @param string[] $availableSortFields
     */
    private function validateSortings(SortBag $sortBag, array $availableSortFields): void
    {
        if (empty($availableSortFields)) {
            return;
        }

        foreach ($sortBag as $sort) {
            if (!\in_array($sort->getField(), $availableSortFields, true)) {
                $this->errors[] = ValidationErrorObject::createByValidatorResult(
                    'order_by',
                    new ValidatorResult(false, \sprintf('Invalid sort field "%s"', $sort->getField()))
                );
            }
        }
    }

    /**
     * @param array<string, FileProperty> $fileProperties
     */
    private function validateFiles(array $fileProperties, FileBag $fileBag): void
    {
        foreach ($fileProperties as $property) {
            $file = $fileBag->get($property->getPropertyName());

            if ($file === null) {
                if ($property->isRequired()) {
                    $this->errors[] = ValidationErrorObject::createByValidatorResult(
                        $property->getPropertyName(),
                        new ValidatorResult(false, ValidatorResult::FIELD_IS_REQUIRED)
                    );
                }

                continue;
            }

            $validatorResult = (new FileValidator($property))->validate($file);

            if (!$validatorResult->isSuccess()) {
                $this->errors[] =
                    ValidationErrorObject::createByValidatorResult($property->getPropertyName(), $validatorResult);
            }
        }
    }

    private function validateFilters(FilterBag $filterBag): void
    {
        foreach ($filterBag->getViolations() as $violation) {
            if ($violation['operator'] === '') {
                $this->errors[] = ValidationErrorObject::createByValidatorResult(
                    $violation['field'],
                    new ValidatorResult(false, ValidatorResult::FILTER_SENT_TWICE)
                );

                continue;
            }

            $this->errors[] = ValidationErrorObject::createByValidatorResult(
                \sprintf('%s[%s]', $violation['field'], $violation['operator']),
                new ValidatorResult(false, ValidatorResult::FILTER_OPERATOR_IS_NOT_ALLOWED)
            );
        }

        /** @var FilterInterface $filter */
        foreach ($filterBag->getIterator() as $filter) {
            $conditions = $filter->conditions();

            if ($conditions === []) {
                if ($filter->getProperty()->isRequired()) {
                    $this->errors[] = ValidationErrorObject::createByValidatorResult(
                        $filter->getField(),
                        new ValidatorResult(false, ValidatorResult::FIELD_IS_REQUIRED)
                    );
                }

                continue;
            }

            foreach ($conditions as $operator => $value) {
                $this->validateFilterCondition($filter, (string)$operator, $value);
            }

            $this->validateFilterLogic($filter);
        }
    }

    /**
     * @param mixed $value
     */
    private function validateFilterCondition(FilterInterface $filter, string $operator, $value): void
    {
        $parameterName = \sprintf('%s[%s]', $filter->getField(), $operator);

        if ($operator === Operator::NULL) {
            if ($value === null) {
                $this->errors[] = ValidationErrorObject::createByValidatorResult(
                    $parameterName,
                    new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_BOOLEAN)
                );
            }

            return;
        }

        $rawValue = $filter->raw($operator);

        if (\is_array($value)) {
            // A JSON body carries a real list, a query string carries one comma-separated string.
            $rawItems = $rawValue === null ? [] : \array_map('trim', \explode(',', $rawValue));

            if ($value === [] || $rawItems === ['']) {
                $this->errors[] = ValidationErrorObject::createByValidatorResult(
                    $parameterName,
                    new ValidatorResult(false, ValidatorResult::FILTER_RANGE_IS_EMPTY)
                );

                return;
            }

            foreach ($value as $index => $item) {
                $this->runPropertyValidators(
                    $filter,
                    $parameterName,
                    $item,
                    $rawItems[$index] ?? null
                );
            }

            return;
        }

        $this->runPropertyValidators($filter, $parameterName, $value, $rawValue);
    }

    /**
     * @param mixed $value
     */
    private function runPropertyValidators(
        FilterInterface $filter,
        string $parameterName,
        $value,
        ?string $rawValue
    ): void {
        $parameter = new Parameter($filter->getField(), $value, $rawValue);

        foreach ($filter->getProperty()->getValidators() as $validator) {
            $result = $validator->validate($parameter);

            if (!$result->isSuccess()) {
                $this->errors[] = ValidationErrorObject::createByValidatorResult($parameterName, $result);
            }
        }
    }

    /**
     * Combinations that can never match, e.g. `value[gte]=100&value[lte]=10`.
     */
    private function validateFilterLogic(FilterInterface $filter): void
    {
        $conditions = $filter->conditions();

        foreach (self::RANGE_PAIRS as [$lower, $upper]) {
            if (!isset($conditions[$lower], $conditions[$upper])) {
                continue;
            }

            if ($conditions[$lower] > $conditions[$upper]) {
                $this->addFilterLogicError($filter, $lower, $upper);
            }
        }

        if (isset($conditions[Operator::EQ], $conditions[Operator::NEQ])
            && $conditions[Operator::EQ] == $conditions[Operator::NEQ]) {
            $this->addFilterLogicError($filter, Operator::EQ, Operator::NEQ);
        }

        if (!isset($conditions[Operator::EQ])) {
            return;
        }

        $equals = $conditions[Operator::EQ];
        $excluded = [
            [Operator::GT, isset($conditions[Operator::GT]) && $equals <= $conditions[Operator::GT]],
            [Operator::GTE, isset($conditions[Operator::GTE]) && $equals < $conditions[Operator::GTE]],
            [Operator::LT, isset($conditions[Operator::LT]) && $equals >= $conditions[Operator::LT]],
            [Operator::LTE, isset($conditions[Operator::LTE]) && $equals > $conditions[Operator::LTE]],
        ];

        foreach ($excluded as [$operator, $isExcluded]) {
            if ($isExcluded) {
                $this->addFilterLogicError($filter, Operator::EQ, $operator);
            }
        }
    }

    private function addFilterLogicError(FilterInterface $filter, string $left, string $right): void
    {
        $this->errors[] = ValidationErrorObject::createByValidatorResult(
            \sprintf('%s[%s]+[%s]', $filter->getField(), $left, $right),
            new ValidatorResult(false, ValidatorResult::FILTER_RANGE_IS_EMPTY)
        );
    }

    private function validateProperties(
        array $properties,
        ParameterBag $parameterBag
    ): void {
        /** @var AbstractProperty $property */
        foreach ($properties as $property) {
            $parameter = $parameterBag->get($property->getPropertyName());

            if ($parameter === null && !$property->isRequired()) {
                continue;
            }

            if ($parameter === null && $property->isRequired()) {
                $error = ValidationErrorObject::createByValidatorResult(
                    $property->getPropertyName(),
                    new ValidatorResult(false, ValidatorResult::FIELD_IS_REQUIRED)
                );

                $this->errors[] = $error;

                continue;
            }

            foreach ($property->getValidators() as $validator) {
                /** @var ValidatorResult $validatorResult */
                $validatorResult = $validator->validate($parameter);

                if (!$validatorResult->isSuccess()) {
                    $error =
                        ValidationErrorObject::createByValidatorResult($property->getPropertyName(), $validatorResult);

                    $this->errors[] = $error;
                }
            }
        }
    }
}
