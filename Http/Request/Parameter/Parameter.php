<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Parameter;

class Parameter
{
    /** @var string */
    private $name;
    /** @var string|int|float|bool|array|\DateTime|null */
    private $value;
    /** @var string|int|float|bool|array|null */
    private $rawValue;

    public function __construct(string $name, $value, $rawValue)
    {
        $this->name = $name;
        $this->value = $value;
        $this->rawValue = $rawValue;
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

    public function getRawValue()
    {
        return $this->rawValue;
    }
    
    public function setRawValue($rawValue): void
    {
        $this->rawValue = $rawValue;
    }
}
