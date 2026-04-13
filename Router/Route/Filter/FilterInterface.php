<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

interface FilterInterface
{
    public const TYPE_EQUALS = 'equals';
    public const TYPE_IN = 'in';
    public const TYPE_LIKE = 'like';
    public const TYPE_GREATER_THAN = 'greater_than';
    public const TYPE_LESS_THAN = 'less_than';
    public const TYPE_CONTAINS = 'contains';

    public function getField(): string;

    public function getType(): string;

    public function getProperty(): AbstractProperty;

    /**
     * @param mixed $value
     */
    public function setValue($value): void;

    /**
     * @return mixed
     */
    public function getValue();

    public function isTypeEquals(): bool;

    public function isTypeIn(): bool;

    public function isTypeLike(): bool;

    public function isTypeContains(): bool;

    public function isTypeGreaterThan(): bool;

    public function isTypeLessThan(): bool;
}
