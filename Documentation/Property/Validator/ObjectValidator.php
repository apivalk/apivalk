<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Http\Request\Parameter\Parameter;

class ObjectValidator extends AbstractValidator
{
    public function validate(Parameter $parameter): ValidatorResult
    {
        $value = $parameter->getValue();
        if (!\is_string($value)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_AN_OBJECT);
        }

        $decoded = json_decode($value, true);

        if (!\is_array($decoded)) {
            return new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_AN_OBJECT);
        }

        //Todo: finish validator for object
//        /** @var AbstractObjectProperty $objectProperty */
//        $objectProperty = $this->getProperty();
//        $objectProperty->getProperties();

        return new ValidatorResult(true);
    }
}
