<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class FloatProperty extends AbstractProperty
{
    /** @var string */
    public const FORMAT_FLOAT = 'float';
    /** @var string */
    public const FORMAT_DOUBLE = 'double';

    /** @var string */
    private $format;
    /** @var float|null */
    private $minimumValue;
    /** @var float|null */
    private $maximumValue;
    /** @var bool|null */
    private $exclusiveMinimum;
    /** @var bool|null */
    private $exclusiveMaximum;

    public function __construct(
        string $propertyName,
        string $propertyDescription = '',
        string $format = self::FORMAT_DOUBLE
    ) {
        parent::__construct($propertyName, $propertyDescription);

        $this->setFormat($format);
    }

    public function getType(): string
    {
        return 'number';
    }

    public function getPhpType(): string
    {
        return 'float';
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        if (!\in_array($format, [self::FORMAT_FLOAT, self::FORMAT_DOUBLE], true)) {
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
        $this->minimumValue = (float) $minimumValue;

        return $this;
    }

    /**
     * @param float|int $maximumValue
     */
    public function setMaximumValue($maximumValue): self
    {
        $this->maximumValue = (float) $maximumValue;

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

    public function getMinimumValue(): ?float
    {
        return $this->minimumValue;
    }

    public function getMaximumValue(): ?float
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
            'format' => $this->getFormat(),
        ];

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
