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
    /** @var string  */
    public const VALUE_IS_NOT_A_STRING = 'value_is_not_a_string';
    /** @var string */
    public const VALUE_IS_SHORTER_THAN_MIN_LENGTH = 'value_is_shorter_than_min_length';

    /** @var bool */
    private $success;
    /** @var string|null */ // TODO: $message could be enum in modern PHP version
    private $message;

    public function __construct(bool $success, ?string $message = null)
    {
        $this->message = $message;
        $this->success = $success;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
