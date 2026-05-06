<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Sort;

class Sort
{
    /** @var bool */
    private $asc = true;
    /** @var string */
    private $field;
    /** @var bool */
    private $requested = false;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * Default sort order for this field is ascending
     */
    public static function asc(string $field): self
    {
        $order = new self($field);

        $order->asc = true;

        return $order;
    }

    /**
     * Default sort order for this field is descending
     */
    public static function desc(string $field): self
    {
        $order = new self($field);

        $order->asc = false;

        return $order;
    }

    public function isAsc(): bool
    {
        return $this->asc;
    }

    public function isDesc(): bool
    {
        return !$this->asc;
    }

    public function getField(): string
    {
        return $this->field;
    }

    /**
     * True only if this sort was set by the user via `?order_by=…`.
     * False for sorts seeded from the route's default declaration.
     */
    public function isRequested(): bool
    {
        return $this->requested;
    }

    public function markAsRequested(): self
    {
        $this->requested = true;

        return $this;
    }
}
