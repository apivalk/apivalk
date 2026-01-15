<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Http\Request\Parameter\Parameter;

class BooleanValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $value = $parameter->getValue();
        if (\in_array($value, [0, 1, 'true', 'false', true, false], true)) {
            return new ValidatorResult(true);
        }

        return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_BOOLEAN);
    }
}
