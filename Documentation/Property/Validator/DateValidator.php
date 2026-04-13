<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Http\Request\Parameter\Parameter;

class DateValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $rawValue = $parameter->getRawValue();

        $date = \DateTime::createFromFormat('Y-m-d', $rawValue);
        $isValidDate = $date && $date->format('Y-m-d') === $rawValue;

        if (!$isValidDate) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_A_VALID_DATE);
        }

        return new ValidatorResult(true);
    }
}
