<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Response\ValidationErrorObject;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\Parameter;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Sort\SortBag;

class RequestValidationMiddleware implements MiddlewareInterface
{
    /** @var ValidationErrorObject[] */
    private $errors = [];

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

    private function validateFilters(FilterBag $filterBag): void
    {
        /** @var FilterInterface $filter */
        foreach ($filterBag->getIterator() as $filter) {
            $value = $filter->getValue();
            $property = $filter->getProperty();

            if ($value === null && !$property->isRequired()) {
                continue;
            }

            if ($value === null && $property->isRequired()) {
                $this->errors[] = ValidationErrorObject::createByValidatorResult(
                    $filter->getField(),
                    new ValidatorResult(false, ValidatorResult::FIELD_IS_REQUIRED)
                );
                continue;
            }

            $parameter = new Parameter($filter->getField(), $value, (string)$value);

            foreach ($property->getValidators() as $validator) {
                $result = $validator->validate($parameter);
                if (!$result->isSuccess()) {
                    $this->errors[] = ValidationErrorObject::createByValidatorResult($filter->getField(), $result);
                }
            }
        }
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
