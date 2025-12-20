<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

class BooleanValidator extends AbstractValidator
{
    public function validate($value): ValidatorResult
    {
        if (\in_array($value, [0, 1, 'true', 'false', true, false], true)) {
            return new ValidatorResult(true);
        }

        return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_BOOLEAN);
    }
}
