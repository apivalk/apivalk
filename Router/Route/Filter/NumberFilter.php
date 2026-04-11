<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\NumberProperty;

class NumberFilter extends AbstractFilter
{
    /**
     * @return self
     */
    public static function equals(NumberProperty $property): self
    {
        return new self(self::TYPE_EQUALS, $property);
    }

    /**
     * @return self
     */
    public static function in(NumberProperty $property): self
    {
        return new self(self::TYPE_IN, $property);
    }

    /**
     * @return self
     */
    public static function greaterThan(NumberProperty $property): self
    {
        return new self(self::TYPE_GREATER_THAN, $property);
    }

    /**
     * @return self
     */
    public static function lessThan(NumberProperty $property): self
    {
        return new self(self::TYPE_LESS_THAN, $property);
    }

    /**
     * @return float|int|null
     */
    public function getValue()
    {
        if ($this->value === null) {
            return null;
        }

        if ($this->getProperty()->getPhpType() === 'int') {
            return (int)$this->value;
        }

        return (float)$this->value;
    }
}
