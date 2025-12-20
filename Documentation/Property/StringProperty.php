<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class StringProperty extends AbstractProperty
{
    /** @var string */
    public const FORMAT_DATE = 'date';
    /** @var string */
    public const FORMAT_DATE_TIME = 'date-time';
    /** @var string */
    public const FORMAT_PASSWORD = 'password';
    /** @var string */
    public const FORMAT_BYTE = 'byte';
    /** @var string */
    public const FORMAT_BINARY = 'binary';

    /** @var string|null */
    private $default;
    /** @var int|null */
    private $minLength;
    /** @var int|null */
    private $maxLength;
    /** @var string|null */
    private $format;
    /** @var string|null */
    private $pattern;
    /** @var array */
    private $enums = [];

    public function getType(): string
    {
        return 'string';
    }

    public function getPhpType(): string
    {
        if ($this->getFormat() === self::FORMAT_DATE || $this->getFormat() === self::FORMAT_DATE_TIME) {
            return '\DateTime';
        }

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

    public function setFormat(?string $format): self
    {
        if ($format !== null
            && !\in_array(
                $format,
                [
                    self::FORMAT_DATE,
                    self::FORMAT_DATE_TIME,
                    self::FORMAT_PASSWORD,
                    self::FORMAT_BYTE,
                    self::FORMAT_BINARY,
                ],
                true
            )) {
            throw new \InvalidArgumentException(\sprintf('Invalid format "%s"', $format));
        }

        $this->format = $format;

        return $this;
    }

    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @param array $enums array of valid values, for example ['test', 'abc123']
     *
     * @return $this
     */
    public function setEnums(array $enums): self
    {
        $this->enums = $enums;

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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function getEnums(): array
    {
        return $this->enums;
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

        if ($this->getFormat() !== null) {
            $array['format'] = $this->getFormat();
        }

        if ($this->getPattern() !== null) {
            $array['pattern'] = $this->getPattern();
        }

        if (!empty($this->getEnums())) {
            $array['enum'] = $this->getEnums();
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
