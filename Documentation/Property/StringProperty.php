<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class StringProperty extends AbstractProperty
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

    private function stripPhpRegexDelimiters(string $pattern): string
    {
        if (\strlen($pattern) < 2) {
            return $pattern;
        }

        $delimiter = $pattern[0];

        if (ctype_alnum($delimiter) || $delimiter === '\\') {
            return $pattern;
        }

        $closingMap = ['{' => '}', '[' => ']', '(' => ')', '<' => '>'];
        $closing = $closingMap[$delimiter] ?? $delimiter;

        $closingPos = strrpos($pattern, $closing, 1);
        if ($closingPos === false || $closingPos === 0) {
            return $pattern;
        }

        return substr($pattern, 1, $closingPos - 1);
    }

    public function getDocumentationArray(): array
    {
        $array = [
            'type' => $this->getType(),
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
            $array['pattern'] = $this->stripPhpRegexDelimiters($this->getPattern());
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
