<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;

final class ValidatorFactory
{
    public static function create(AbstractProperty $property): AbstractValidator
    {
        if ($property instanceof StringProperty) {
            if (\in_array($property->getFormat(), [StringProperty::FORMAT_DATE_TIME, StringProperty::FORMAT_DATE], true)) {
                return new DatetimeValidator($property);
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
            public function validate($value): ValidatorResult
            {
                return new ValidatorResult(true);
            }
        };
    }
}
