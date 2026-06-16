<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\SimpleArrayProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

/**
 * Validates a one-dimensional array of scalars.
 *
 * Unlike {@see ArrayValidator}, which only checks that the value is an array, this
 * validator also asserts that every element matches the array's configured item type
 * (int, string, number or bool).
 *
 * The raw (pre-cast) value is validated on purpose: type casting coerces elements to the
 * item type (e.g. "abc" would become 0 for an int array), so validating the cast value
 * would let invalid input slip through.
 */
class SimpleArrayValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $value = $parameter->getRawValue();

        if (\is_string($value)) {
            $value = json_decode($value, true);
        }

        if (!\is_array($value)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_AN_ARRAY);
        }

        /** @var SimpleArrayProperty $property */
        $property = $this->getProperty();
        $itemType = $property->getItemType();

        foreach ($value as $item) {
            $itemResult = $this->validateItem($item, $itemType);
            if (!$itemResult->isSuccess()) {
                return $itemResult;
            }
        }

        return new ValidatorResult(true);
    }

    /**
     * @param mixed $item
     */
    private function validateItem($item, string $itemType): ValidatorResult
    {
        switch ($itemType) {
            case SimpleArrayProperty::TYPE_INT:
            case SimpleArrayProperty::TYPE_NUMBER:
                if (!is_numeric($item)) {
                    return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_NUMERIC);
                }

                return new ValidatorResult(true);

            case SimpleArrayProperty::TYPE_BOOL:
                if (!\in_array($item, [0, 1, 'true', 'false', true, false], true)) {
                    return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_BOOLEAN);
                }

                return new ValidatorResult(true);

            case SimpleArrayProperty::TYPE_STRING:
            default:
                if (!\is_string($item)) {
                    return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_A_STRING);
                }

                return new ValidatorResult(true);
        }
    }
}
