<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * Accessor name to operator. The accessor is what a controller reads
     * (`$filters->status->equal`), the operator value is what travels on the wire
     * (`?status[eq]=active`).
     *
     * @var array<string, string>
     */
    public const ACCESSORS = [
        'equal' => Operator::EQ,
        'notEqual' => Operator::NEQ,
        'in' => Operator::IN,
        'greaterThan' => Operator::GT,
        'greaterThanOrEqual' => Operator::GTE,
        'lessThan' => Operator::LT,
        'lessThanOrEqual' => Operator::LTE,
        'like' => Operator::LIKE,
        'contains' => Operator::CONTAINS,
        'isNull' => Operator::NULL,
    ];

    protected AbstractProperty $property;

    /** @var string[] */
    private array $allowedOperators;

    /** @var array<string, mixed> */
    private array $values = [];

    /** @var array<string, string|null> */
    private array $rawValues = [];

    /**
     * @param Operator::* ...$operators
     */
    public function __construct(AbstractProperty $property, string ...$operators)
    {
        if ($operators === []) {
            throw new \InvalidArgumentException(\sprintf(
                'Filter %s on field "%s" declares no operator. Pass at least one, e.g. Operator::EQ.',
                static::class,
                $property->getPropertyName()
            ));
        }

        $supported = static::supportedOperators();

        foreach ($operators as $operator) {
            if (!\in_array($operator, $supported, true)) {
                throw new \InvalidArgumentException(\sprintf(
                    'Operator "%s" is not supported by %s on field "%s". Supported: %s.',
                    $operator,
                    static::class,
                    $property->getPropertyName(),
                    \implode(', ', $supported)
                ));
            }
        }

        if (\count($operators) !== \count(\array_unique($operators))) {
            throw new \InvalidArgumentException(\sprintf(
                'Filter %s on field "%s" declares a duplicate operator.',
                static::class,
                $property->getPropertyName()
            ));
        }

        $this->property = $property->init();
        $this->allowedOperators = $operators;
    }

    public function getField(): string
    {
        return $this->property->getPropertyName();
    }

    public function getProperty(): AbstractProperty
    {
        return $this->property;
    }

    /** @return string[] */
    public function getAllowedOperators(): array
    {
        return $this->allowedOperators;
    }

    public function getDefaultOperator(): string
    {
        return $this->allowedOperators[0];
    }

    public function allows(string $operator): bool
    {
        return \in_array($operator, $this->allowedOperators, true);
    }

    public function has(string $operator): bool
    {
        return \array_key_exists($operator, $this->values);
    }

    public function raw(string $operator): ?string
    {
        return $this->rawValues[$operator] ?? null;
    }

    /**
     * @param mixed $value
     */
    public function setCondition(string $operator, $value, ?string $rawValue): void
    {
        if (!$this->allows($operator)) {
            throw new \InvalidArgumentException(\sprintf(
                'Operator "%s" is not allowed on field "%s". Allowed: %s.',
                $operator,
                $this->getField(),
                \implode(', ', $this->allowedOperators)
            ));
        }

        $this->values[$operator] = $value;
        $this->rawValues[$operator] = $rawValue;
    }

    /** @return array<string, mixed> */
    public function conditions(): array
    {
        return $this->values;
    }

    /**
     * Operator values are read as properties: `$filters->price->greaterThan`. The generated
     * shape interface types them, and keeping them off the class means autocompletion offers
     * the operators the field declares rather than every operator its type could express.
     *
     * @return mixed
     */
    public function __get(string $accessor)
    {
        if (!isset(self::ACCESSORS[$accessor])) {
            throw new \LogicException(\sprintf(
                'Unknown filter accessor "%s" on field "%s". Available: %s.',
                $accessor,
                $this->getField(),
                \implode(', ', $this->accessorNames())
            ));
        }

        $operator = self::ACCESSORS[$accessor];

        if (!$this->allows($operator)) {
            throw new \LogicException(\sprintf(
                'Operator "%s" is not declared on field "%s". Declared: %s.',
                $operator,
                $this->getField(),
                \implode(', ', $this->accessorNames())
            ));
        }

        if ($operator === Operator::IN) {
            return $this->values[$operator] ?? [];
        }

        return $this->values[$operator] ?? null;
    }

    public function __isset(string $accessor): bool
    {
        return isset(self::ACCESSORS[$accessor]) && $this->allows(self::ACCESSORS[$accessor]);
    }

    /** @return string[] */
    private function accessorNames(): array
    {
        $names = [];

        foreach ($this->allowedOperators as $operator) {
            $names[] = (string)array_search($operator, self::ACCESSORS, true);
        }

        return $names;
    }
}
