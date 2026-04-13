<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;

class StringFilter implements FilterInterface
{
    /** @var string */
    private $type;
    /** @var StringProperty */
    private $property;
    /** @var string|null */
    private $value;

    public function __construct(string $type, StringProperty $property)
    {
        $this->type = $type;
        $this->property = $property;
    }

    public static function equals(StringProperty $property): self
    {
        return new self(self::TYPE_EQUALS, $property);
    }

    public static function in(StringProperty $property): self
    {
        return new self(self::TYPE_IN, $property);
    }

    public static function like(StringProperty $property): self
    {
        return new self(self::TYPE_LIKE, $property);
    }

    public static function contains(StringProperty $property): self
    {
        return new self(self::TYPE_CONTAINS, $property);
    }

    public function getField(): string
    {
        return $this->property->getPropertyName();
    }

    public function getType(): string
    {
        return $this->type;
    }

    /** @return StringProperty */
    public function getProperty(): AbstractProperty
    {
        return $this->property;
    }

    public function setValue($value): void
    {
        $this->value = $value !== null ? (string) $value : null;
    }

    public function getValue(): ?string
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
