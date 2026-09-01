<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\BooleanProperty;

class BooleanFilter extends AbstractFilter
{
    /**
     * @param Operator::* ...$operators
     */
    public function __construct(BooleanProperty $property, string ...$operators)
    {
        parent::__construct($property, ...$operators);
    }

    /** @return string[] */
    public static function supportedOperators(): array
    {
        return [
            Operator::EQ,
            Operator::NULL,
        ];
    }
}
