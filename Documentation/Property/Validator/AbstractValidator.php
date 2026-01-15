<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

abstract class AbstractValidator
{
    /** @var AbstractProperty */
    private $property;

    /**
     * @param Parameter $parameter
     *
     * @return ValidatorResult
     */
    abstract public function validate(Parameter $parameter): ValidatorResult;

    public function __construct(AbstractProperty $property)
    {
        $this->property = $property;
    }

    public function getProperty(): AbstractProperty
    {
        return $this->property;
    }
}
