<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\StringProperty;

class StringFilter extends AbstractFilter
{
    /**
     * @return self
     */
    public static function equals(StringProperty $property): self
    {
        return new self(self::TYPE_EQUALS, $property);
    }

    /**
     * @return self
     */
    public static function in(StringProperty $property): self
    {
        return new self(self::TYPE_IN, $property);
    }

    /**
     * @return self
     */
    public static function like(StringProperty $property): self
    {
        return new self(self::TYPE_LIKE, $property);
    }

    /**
     * @return self
     */
    public static function contains(StringProperty $property): self
    {
        return new self(self::TYPE_CONTAINS, $property);
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        if ($this->value !== null) {
            return (string)$this->value;
        }

        return null;
    }
}
