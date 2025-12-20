<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Parameter;

class Parameter
{
    /** @var string */
    private $name;
    /** @var string|int|float|bool|array|null */
    private $value;

    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }
}
