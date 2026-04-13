<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class DateProperty extends AbstractProperty
{
    /** @var string|null */
    private $default;

    public function getType(): string
    {
        return 'string';
    }

    public function getFormat(): string
    {
        return 'date';
    }

    public function getPhpType(): string
    {
        return '\DateTime';
    }

    public function getPattern(): string
    {
        return '^\d{4}-\d{2}-\d{2}$';
    }

    public function setDefault(?string $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getDocumentationArray(): array
    {
        $array = [
            'type' => $this->getType(),
            'format' => $this->getFormat(),
            'pattern' => $this->getPattern(),
        ];

        if ($this->getDefault() !== null) {
            $array['default'] = $this->getDefault();
        }

        if ($this->getPropertyDescription() !== '') {
            $array['description'] = $this->getPropertyDescription();
        }

        if ($this->getExample() !== null) {
            $array['example'] = $this->getExample();
        }

        return $array;
    }
}
