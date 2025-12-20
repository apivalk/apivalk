<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class NumberProperty extends AbstractProperty
{
    /** @var string */
    public const FORMAT_FLOAT = 'float';
    /** @var string */
    public const FORMAT_DOUBLE = 'double';
    /** @var string */
    public const FORMAT_INT32 = 'int32';
    /** @var string */
    public const FORMAT_INT64 = 'int64';

    /** @var string|null */
    private $format;
    /** @var int|float|null */
    private $minimumValue;
    /** @var int|float|null */
    private $maximumValue;
    /** @var bool|null */
    private $exclusiveMinimum;
    /** @var bool|null */
    private $exclusiveMaximum;

    public function __construct(
        string $propertyName,
        string $propertyDescription = '',
        ?string $format = null
    ) {
        parent::__construct($propertyName, $propertyDescription);

        if ($format !== null) {
            $this->setFormat($format);
        }
    }

    public function getType(): string
    {
        if ($this->getFormat() === null
            || $this->getFormat() === self::FORMAT_FLOAT
            || $this->getFormat() === self::FORMAT_DOUBLE) {
            return 'number';
        }

        return 'integer';
    }

    public function getPhpType(): string
    {
        if ($this->getFormat() === self::FORMAT_INT32
            || $this->getFormat() === self::FORMAT_INT64) {
            return 'int';
        }

        return 'float';
    }

    public function setFormat(?string $format): self
    {
        if ($format !== null
            && !\in_array(
                $format,
                [
                    self::FORMAT_FLOAT,
                    self::FORMAT_DOUBLE,
                    self::FORMAT_INT32,
                    self::FORMAT_INT64,
                ],
                true
            )) {
            throw new \InvalidArgumentException(\sprintf('Invalid format "%s"', $format));
        }

        $this->format = $format;

        return $this;
    }

    /**
     * @param float|int $minimumValue
     */
    public function setMinimumValue($minimumValue): self
    {
        $this->minimumValue = $minimumValue;

        return $this;
    }

    /**
     * @param float|int $maximumValue
     */
    public function setMaximumValue($maximumValue): self
    {
        $this->maximumValue = $maximumValue;

        return $this;
    }

    public function setIsExclusiveMinimum(?bool $exclusiveMinimum): self
    {
        $this->exclusiveMinimum = $exclusiveMinimum;

        return $this;
    }

    public function setIsExclusiveMaximum(?bool $exclusiveMaximum): self
    {
        $this->exclusiveMaximum = $exclusiveMaximum;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @return float|int|null
     */
    public function getMinimumValue()
    {
        return $this->minimumValue;
    }

    /**
     * @return float|int|null
     */
    public function getMaximumValue()
    {
        return $this->maximumValue;
    }

    public function isExclusiveMinimum(): ?bool
    {
        return $this->exclusiveMinimum;
    }

    public function isExclusiveMaximum(): ?bool
    {
        return $this->exclusiveMaximum;
    }

    public function getDocumentationArray(): array
    {
        $array = [
            'type' => $this->getType(),
        ];

        if ($this->getFormat() !== null) {
            $array['format'] = $this->getFormat();
        }

        if ($this->getMinimumValue() !== null && $this->isExclusiveMinimum() !== null) {
            $array['exclusiveMinimum'] = $this->isExclusiveMinimum();
        }

        if ($this->getMaximumValue() !== null && $this->isExclusiveMaximum() !== null) {
            $array['exclusiveMaximum'] = $this->isExclusiveMaximum();
        }

        if ($this->getMinimumValue() !== null) {
            $array['minimum'] = $this->getMinimumValue();
        }

        if ($this->getMaximumValue() !== null) {
            $array['maximum'] = $this->getMaximumValue();
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
