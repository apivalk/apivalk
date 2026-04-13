<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;

class IntegerFilter implements FilterInterface
{
    /** @var string */
    private $type;
    /** @var IntegerProperty */
    private $property;
    /** @var int|null */
    private $value;

    public function __construct(string $type, IntegerProperty $property)
    {
        $this->type = $type;
        $this->property = $property;
    }

    public static function equals(IntegerProperty $property): self
    {
        return new self(self::TYPE_EQUALS, $property);
    }

    public static function in(IntegerProperty $property): self
    {
        return new self(self::TYPE_IN, $property);
    }

    public static function greaterThan(IntegerProperty $property): self
    {
        return new self(self::TYPE_GREATER_THAN, $property);
    }

    public static function lessThan(IntegerProperty $property): self
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

    /** @return IntegerProperty */
    public function getProperty(): AbstractProperty
    {
        return $this->property;
    }

    public function setValue($value): void
    {
        $this->value = $value !== null ? (int) $value : null;
    }

    public function getValue(): ?int
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
