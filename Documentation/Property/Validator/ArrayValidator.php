<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Http\Request\Parameter\Parameter;

class ArrayValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $value = $parameter->getValue();
        if (\is_array($value)) {
            return new ValidatorResult(true);
        }

        if (!\is_string($value)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_AN_ARRAY);
        }

        $array = json_decode($value, true);

        if (\is_array($array)) {
            return new ValidatorResult(true);
        }

        return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_AN_ARRAY);
    }
}
