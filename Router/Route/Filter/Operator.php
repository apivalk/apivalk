<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

/**
 * Filter operators. The constant values are the wire names, so `Operator::GTE`
 * is what a client sends as `field[gte]=…`.
 *
 * Every constant here is a plain operator string, which is what makes the
 * `Operator::*` PHPDoc type usable as a parameter type.
 */
final class Operator
{
    /** @var string */
    public const EQ = 'eq';
    /** @var string */
    public const NEQ = 'neq';
    /** @var string */
    public const IN = 'in';
    /** @var string */
    public const GT = 'gt';
    /** @var string */
    public const GTE = 'gte';
    /** @var string */
    public const LT = 'lt';
    /** @var string */
    public const LTE = 'lte';
    /** @var string */
    public const LIKE = 'like';
    /** @var string */
    public const CONTAINS = 'contains';
    /** @var string */
    public const NULL = 'null';

    /**
     * The controller-facing accessor for an operator, e.g. `gte` reads as
     * `$filters->price->greaterThanOrEqual`.
     */
    public static function accessorFor(string $operator): string
    {
        $accessor = array_search($operator, AbstractFilter::ACCESSORS, true);

        if ($accessor === false) {
            throw new \InvalidArgumentException(\sprintf('Unknown operator "%s"', $operator));
        }

        return $accessor;
    }

    private function __construct()
    {
    }
}
