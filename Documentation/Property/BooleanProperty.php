<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class BooleanProperty extends AbstractProperty
{
    /** @var bool */
    private $default;

    public function __construct(
        string $propertyName,
        string $propertyDescription = '',
        bool $default
    ) {
        parent::__construct($propertyName, $propertyDescription);

        $this->default = $default;
    }

    public function getType(): string
    {
        return 'boolean';
    }

    public function getPhpType(): string
    {
        return 'bool';
    }

    public function getDefault(): bool
    {
        return $this->default;
    }

    public function getDocumentationArray(): array
    {
        $array = [
            'type' => $this->getType(),
            'default' => $this->getDefault(),
        ];

        if ($this->getPropertyDescription() !== '') {
            $array['description'] = $this->getPropertyDescription();
        }

        if ($this->getExample() !== null) {
            $array['example'] = $this->getExample();
        }

        return $array;
    }
}
