<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Http\Request\Parameter\Parameter;

class DateTimeValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $rawValue = $parameter->getRawValue();

        $dateTime = \DateTime::createFromFormat(\DateTimeInterface::RFC3339, $rawValue);
        $errors = \DateTime::getLastErrors();
        $isValidDateTime = $dateTime instanceof \DateTime && $errors['warning_count'] === 0 && $errors['error_count'] === 0;

        if (!$isValidDateTime) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_A_VALID_DATE_TIME);
        }

        return new ValidatorResult(true);
    }
}
