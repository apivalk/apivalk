<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class BinaryValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $value = $parameter->getValue();
        if (!\is_string($value)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_A_STRING);
        }

        /** @var BinaryProperty $property */
        $property = $this->getProperty();

        $minLength = $property->getMinLength();
        $maxLength = $property->getMaxLength();
        $pattern = $property->getPattern();

        if ($minLength !== null && \mb_strlen($value) < $minLength) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_SHORTER_THAN_MIN_LENGTH);
        }

        if ($maxLength !== null && \mb_strlen($value) > $maxLength) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_LONGER_THAN_MAX_LENGTH);
        }

        if ($pattern !== null && !preg_match($pattern, $value)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_DOES_NOT_MATCH_PATTERN);
        }

        return new ValidatorResult(true);
    }
}
