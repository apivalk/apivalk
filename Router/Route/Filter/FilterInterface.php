<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

interface FilterInterface
{
    public function getField(): string;

    /** @internal */
    public function getProperty(): AbstractProperty;

    /**
     * Operators this filter class can express at all, regardless of what a route allows.
     *
     * @internal
     *
     * @return string[]
     */
    public static function supportedOperators(): array;

    /**
     * Operators allowed on this field, in declaration order.
     *
     * @internal
     *
     * @return string[]
     */
    public function getAllowedOperators(): array;

    /**
     * The operator flat notation resolves to: `?status=active` means `status[eq]=active`
     * when EQ is declared first.
     *
     * @internal
     */
    public function getDefaultOperator(): string;

    /**
     * @internal
     *
     * @param Operator::* $operator
     */
    public function allows(string $operator): bool;

    /**
     * True when the client supplied this operator.
     *
     * @param Operator::* $operator
     */
    public function has(string $operator): bool;

    /**
     * The value the client sent, verbatim.
     *
     * @param Operator::* $operator
     */
    public function raw(string $operator): ?string;

    /**
     * @internal
     *
     * @param Operator::* $operator
     * @param mixed       $value
     */
    public function setCondition(string $operator, $value, ?string $rawValue): void;

    /**
     * Supplied conditions as operator => cast value, in wire order.
     *
     * @return array<string, mixed>
     */
    public function conditions(): array;
}
