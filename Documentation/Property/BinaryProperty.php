<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class BinaryProperty extends AbstractProperty
{
    /** @var string|null */
    private $default;
    /** @var int|null */
    private $minLength;
    /** @var int|null */
    private $maxLength;
    /** @var string|null */
    private $pattern;

    public function getType(): string
    {
        return 'string';
    }

    public function getFormat(): string
    {
        return 'binary';
    }

    public function getPhpType(): string
    {
        return 'string';
    }

    public function setDefault(?string $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function setMinLength(?int $minLength): self
    {
        $this->minLength = $minLength;

        return $this;
    }

    public function setMaxLength(?int $maxLength): self
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function getDocumentationArray(): array
    {
        $array = [
            'type' => $this->getType(),
            'format' => $this->getFormat(),
        ];

        if ($this->getDefault() !== null) {
            $array['default'] = $this->getDefault();
        }

        if ($this->getMinLength() !== null) {
            $array['minLength'] = $this->getMinLength();
        }

        if ($this->getMaxLength() !== null) {
            $array['maxLength'] = $this->getMaxLength();
        }

        if ($this->getPattern() !== null) {
            $array['pattern'] = $this->getPattern();
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
