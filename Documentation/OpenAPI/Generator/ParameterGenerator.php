<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\OpenAPI\Object\ParameterObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SingleSchemaObject;
use apivalk\apivalk\Documentation\Property\AbstractProperty;

class ParameterGenerator
{
    public function generate(AbstractProperty $property, string $in): ParameterObject
    {
        return new ParameterObject(
            $property->getPropertyName(),
            $in,
            $property->getPropertyDescription(),
            $property->isRequired(),
            new SingleSchemaObject($property->getPropertyName(), $property->getType(), $property->isRequired())
        );
    }
}
