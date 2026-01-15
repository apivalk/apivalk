<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\StringProperty;

class DatetimeValidator extends AbstractValidator
{
    public function validate($value): ValidatorResult
    {
        /** @var StringProperty $property */
        $property = $this->getProperty();
        $format = $property->getFormat();

        if ($format === $property::FORMAT_DATE) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            $isValidDate = $date && $date->format('Y-m-d') === $value;

            if (!$isValidDate) {
                return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_A_VALID_DATE);
            }
        }

        if ($format === $property::FORMAT_DATE_TIME) {
            $dateTime = \DateTime::createFromFormat(\DateTimeInterface::RFC3339, $value);
            if (!$dateTime) {
                $dateTime = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $value);
            }

            $errors = \DateTime::getLastErrors();
            $isValidDateTime = $dateTime instanceof \DateTime && $errors['warning_count'] === 0 && $errors['error_count'] === 0;

            if (!$isValidDateTime) {
                return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_A_VALID_DATE_TIME);
            }
        }

        return new ValidatorResult(true);
    }
}
