<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

final class ValidatorFactory
{
    public static function create(AbstractProperty $property): AbstractValidator
    {
        if ($property instanceof StringProperty) {
            if (\in_array($property->getFormat(), [StringProperty::FORMAT_DATE, StringProperty::FORMAT_DATE_TIME], true)) {
                return new DateTimeValidator($property);
            }

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

        if ($property instanceof NumberProperty) {
            return new NumberValidator($property);
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
