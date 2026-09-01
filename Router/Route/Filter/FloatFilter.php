<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\FloatProperty;

class FloatFilter extends AbstractFilter
{
    /**
     * @param Operator::* ...$operators
     */
    public function __construct(FloatProperty $property, string ...$operators)
    {
        parent::__construct($property, ...$operators);
    }

    /** @return string[] */
    public static function supportedOperators(): array
    {
        return [
            Operator::EQ,
            Operator::NEQ,
            Operator::IN,
            Operator::GT,
            Operator::GTE,
            Operator::LT,
            Operator::LTE,
            Operator::NULL,
        ];
    }
}
