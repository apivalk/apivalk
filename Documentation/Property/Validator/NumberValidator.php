<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class NumberValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $value = $parameter->getValue();
        if (!is_numeric($value)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_NUMERIC);
        }

        /** @var NumberProperty $property */
        $property = $this->getProperty();

        $format = $property->getFormat();
        $minimumValue = $property->getMinimumValue();
        $maximumValue = $property->getMaximumValue();

        if ($format === $property::FORMAT_INT32
            || $format === $property::FORMAT_INT64) {
            $value = (int)$value;
        } else {
            $value = (float)$value;
        }

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
