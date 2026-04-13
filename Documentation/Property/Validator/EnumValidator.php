<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class EnumValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $value = $parameter->getValue();
        if (!\is_string($value)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_A_STRING);
        }

        /** @var EnumProperty $property */
        $property = $this->getProperty();

        $enums = $property->getEnums();

        if (\count($enums) > 0 && !\in_array($value, $enums, true)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_A_VALID_ENUM_VALUE);
        }

        return new ValidatorResult(true);
    }
}
