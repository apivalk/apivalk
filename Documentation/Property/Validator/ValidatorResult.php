<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

class ValidatorResult
{
    /** @var string */
    public const FIELD_IS_REQUIRED = 'field_is_required';
    /** @var string */
    public const VALUE_DOES_NOT_MATCH_PATTERN = 'value_does_not_match_pattern';
    /** @var string */
    public const VALUE_IS_HIGHER_THAN_MAXIMUM = 'value_is_higher_than_maximum';
    /** @var string */
    public const VALUE_IS_LONGER_THAN_MAX_LENGTH = 'value_is_longer_than_max_length';
    /** @var string */
    public const VALUE_IS_LOWER_THAN_MINIMUM = 'value_is_lower_than_minimum';
    /** @var string */
    public const VALUE_IS_NOT_A_VALID_BASE64_STRING = 'value_is_not_a_valid_base64_string';
    /** @var string */
    public const VALUE_IS_NOT_A_VALID_DATE = 'value_is_not_a_valid_date';
    /** @var string */
    public const VALUE_IS_NOT_A_VALID_DATE_TIME = 'value_is_not_a_valid_date_time';
    /** @var string */
    public const VALUE_IS_NOT_A_VALID_ENUM_VALUE = 'value_is_not_a_valid_enum_value';
    /** @var string */
    public const VALUE_IS_NOT_AN_ARRAY = 'value_is_not_an_array';
    /** @var string */
    public const VALUE_IS_NOT_AN_OBJECT = 'value_is_not_an_object';
    /** @var string */
    public const VALUE_IS_NOT_BOOLEAN = 'value_is_not_boolean';
    /** @var string */
    public const VALUE_IS_NOT_NUMERIC = 'value_is_not_numeric';
    /** @var string */
    public const VALUE_IS_NOT_A_STRING = 'value_is_not_a_string';
    /** @var string */
    public const VALUE_IS_SHORTER_THAN_MIN_LENGTH = 'value_is_shorter_than_min_length';

    /** @var bool */
    private $success;
    /** @var string|null */
    private $errorKey;

    public function __construct(bool $success, ?string $errorKey = null)
    {
        $this->errorKey = $errorKey;
        $this->success = $success;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorKey(): ?string
    {
        return $this->errorKey;
    }

    public function getLocalizedErrorMessage(): string
    {
        switch ($this->errorKey) {
            case self::FIELD_IS_REQUIRED:
                return 'This field is required.';

            case self::VALUE_DOES_NOT_MATCH_PATTERN:
                return 'This value does not match the required pattern.';

            case self::VALUE_IS_HIGHER_THAN_MAXIMUM:
                return 'This value is higher than the maximum allowed value.';

            case self::VALUE_IS_LONGER_THAN_MAX_LENGTH:
                return 'This value is longer than the maximum length.';

            case self::VALUE_IS_LOWER_THAN_MINIMUM:
                return 'This value is lower than the minimum allowed value.';

            case self::VALUE_IS_NOT_A_VALID_BASE64_STRING:
                return 'This value is not a valid base64 string.';

            case self::VALUE_IS_NOT_A_VALID_DATE:
                return 'This value is not a valid date.';

            case self::VALUE_IS_NOT_A_VALID_DATE_TIME:
                return 'This value is not a valid date-time.';

            case self::VALUE_IS_NOT_A_VALID_ENUM_VALUE:
                return 'This value is not one of the allowed values.';

            case self::VALUE_IS_NOT_AN_ARRAY:
                return 'This value is not an array.';

            case self::VALUE_IS_NOT_AN_OBJECT:
                return 'This value is not an object.';

            case self::VALUE_IS_NOT_BOOLEAN:
                return 'This value is not a boolean.';

            case self::VALUE_IS_NOT_NUMERIC:
                return 'This value is not numeric.';

            case self::VALUE_IS_NOT_A_STRING:
                return 'This value is not a string.';

            case self::VALUE_IS_SHORTER_THAN_MIN_LENGTH:
                return 'This value is shorter than the minimum length.';
        }

        return $this->errorKey;
    }
}
