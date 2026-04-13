<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class FloatValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $value = $parameter->getValue();
        if (!is_numeric($value)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_NUMERIC);
        }

        $value = (float) $value;

        /** @var FloatProperty $property */
        $property = $this->getProperty();

        $minimumValue = $property->getMinimumValue();
        $maximumValue = $property->getMaximumValue();

        if ($minimumValue !== null) {
            $minimumValidation = $property->isExclusiveMinimum() === true
                ? $value > $minimumValue
                : $value >= $minimumValue;

            if (!$minimumValidation) {
                return new ValidatorResult(false, ValidatorResult::VALUE_IS_LOWER_THAN_MINIMUM);
            }
        }

        if ($maximumValue !== null) {
            $maximumValidation = $property->isExclusiveMaximum() === true
                ? $value < $maximumValue
                : $value <= $maximumValue;

            if (!$maximumValidation) {
                return new ValidatorResult(false, ValidatorResult::VALUE_IS_HIGHER_THAN_MAXIMUM);
            }
        }

        return new ValidatorResult(true);
    }
}
