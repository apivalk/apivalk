<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\StringProperty;

class DateTimeFilter extends AbstractFilter
{
    public function __construct(string $type, StringProperty $property)
    {
        if ($property->getFormat() !== StringProperty::FORMAT_DATE_TIME) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'DateTimeFilter requires a StringProperty with FORMAT_DATE_TIME, "%s" given',
                    $property->getFormat()
                )
            );
        }

        parent::__construct($type, $property);
    }

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
    public static function greaterThan(StringProperty $property): self
    {
        return new self(self::TYPE_GREATER_THAN, $property);
    }

    /**
     * @return self
     */
    public static function lessThan(StringProperty $property): self
    {
        return new self(self::TYPE_LESS_THAN, $property);
    }

    /**
     * @return \DateTime|null
     */
    public function getValue(): ?\DateTime
    {
        if ($this->value === null) {
            return null;
        }

        try {
            return new \DateTime((string)$this->value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
