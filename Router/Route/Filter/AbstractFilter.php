<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

abstract class AbstractFilter
{
    public const TYPE_EQUALS = 'equals';
    public const TYPE_IN = 'in';
    public const TYPE_LIKE = 'like';
    public const TYPE_GREATER_THAN = 'greater_than';
    public const TYPE_LESS_THAN = 'less_than';
    public const TYPE_CONTAINS = 'contains';

    /** @var string */
    protected $type;
    /** @var AbstractProperty */
    protected $property;
    /** @var mixed */
    protected $value;

    public function __construct(string $type, AbstractProperty $property)
    {
        $this->type = $type;
        $this->property = $property;
    }

    public function getField(): string
    {
        return $this->property->getPropertyName();
    }

    public function getType(): string
    {
        return $this->type;
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

    public function getProperty(): AbstractProperty
    {
        return $this->property;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
