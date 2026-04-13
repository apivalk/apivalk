<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\FloatProperty;

class FloatFilter implements FilterInterface
{
    /** @var string */
    private $type;
    /** @var FloatProperty */
    private $property;
    /** @var float|null */
    private $value;

    public function __construct(string $type, FloatProperty $property)
    {
        $this->type = $type;
        $this->property = $property;
    }

    public static function equals(FloatProperty $property): self
    {
        return new self(self::TYPE_EQUALS, $property);
    }

    public static function in(FloatProperty $property): self
    {
        return new self(self::TYPE_IN, $property);
    }

    public static function greaterThan(FloatProperty $property): self
    {
        return new self(self::TYPE_GREATER_THAN, $property);
    }

    public static function lessThan(FloatProperty $property): self
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

    /** @return FloatProperty */
    public function getProperty(): AbstractProperty
    {
        return $this->property;
    }

    public function setValue($value): void
    {
        $this->value = $value !== null ? (float) $value : null;
    }

    public function getValue(): ?float
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
