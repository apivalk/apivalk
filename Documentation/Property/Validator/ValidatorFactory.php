<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\ByteProperty;
use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

final class ValidatorFactory
{
    public static function create(AbstractProperty $property): AbstractValidator
    {
        if ($property instanceof DateProperty) {
            return new DateValidator($property);
        }

        if ($property instanceof DateTimeProperty) {
            return new DateTimeValidator($property);
        }

        if ($property instanceof EnumProperty) {
            return new EnumValidator($property);
        }

        if ($property instanceof ByteProperty) {
            return new ByteValidator($property);
        }

        if ($property instanceof BinaryProperty) {
            return new BinaryValidator($property);
        }

        if ($property instanceof StringProperty) {
            return new StringValidator($property);
        }

        if ($property instanceof ArrayProperty) {
            return new ArrayValidator($property);
        }

        if ($property instanceof AbstractObjectProperty) {
            return new ObjectValidator($property);
        }

        if ($property instanceof BooleanProperty) {
            return new BooleanValidator($property);
        }

        if ($property instanceof IntegerProperty) {
            return new IntegerValidator($property);
        }

        if ($property instanceof FloatProperty) {
            return new FloatValidator($property);
        }

        // Default validator for unknown properties (e.g. anonymous classes in tests)
        return new class($property) extends AbstractValidator {
            public function validate(Parameter $parameter): ValidatorResult
            {
                return new ValidatorResult(true);
            }
        };
    }
}
