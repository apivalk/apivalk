<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\DateTimeProperty;

class DateTimeFilter implements FilterInterface
{
    /** @var string */
    private $type;
    /** @var DateTimeProperty */
    private $property;
    /** @var \DateTime|null */
    private $value;

    public function __construct(string $type, DateTimeProperty $property)
    {
        $this->type = $type;
        $this->property = $property;
    }

    public static function equals(DateTimeProperty $property): self
    {
        return new self(self::TYPE_EQUALS, $property);
    }

    public static function in(DateTimeProperty $property): self
    {
        return new self(self::TYPE_IN, $property);
    }

    public static function greaterThan(DateTimeProperty $property): self
    {
        return new self(self::TYPE_GREATER_THAN, $property);
    }

    public static function lessThan(DateTimeProperty $property): self
    {
        return new self(self::TYPE_LESS_THAN, $property);
    }

    public function getField(): string
    {
        return $this->property->getPropertyName();
    }

    public function getType(): string
    {
        return $this->type;
    }

    /** @return DateTimeProperty */
    public function getProperty(): AbstractProperty
    {
        return $this->property;
    }

    public function setValue($value): void
    {
        if ($value instanceof \DateTime) {
            $this->value = $value;

            return;
        }

        if ($value === null) {
            $this->value = null;

            return;
        }

        try {
            $this->value = new \DateTime((string) $value);
        } catch (\Exception $e) {
            $this->value = null;
        }
    }

    public function getValue(): ?\DateTime
    {
        return $this->value;
    }

    public function isTypeEquals(): bool
    {
        return $this->type === self::TYPE_EQUALS;
    }

    public function isTypeIn(): bool
    {
        return $this->type === self::TYPE_IN;
    }

    public function isTypeLike(): bool
    {
        return $this->type === self::TYPE_LIKE;
    }

    public function isTypeContains(): bool
    {
        return $this->type === self::TYPE_CONTAINS;
    }

    public function isTypeGreaterThan(): bool
    {
        return $this->type === self::TYPE_GREATER_THAN;
    }

    public function isTypeLessThan(): bool
    {
        return $this->type === self::TYPE_LESS_THAN;
    }
}
