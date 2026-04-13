<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class IntegerProperty extends AbstractProperty
{
    /** @var string */
    public const FORMAT_INT32 = 'int32';
    /** @var string */
    public const FORMAT_INT64 = 'int64';

    /** @var string */
    private $format;
    /** @var int|null */
    private $minimumValue;
    /** @var int|null */
    private $maximumValue;
    /** @var bool|null */
    private $exclusiveMinimum;
    /** @var bool|null */
    private $exclusiveMaximum;

    public function __construct(
        string $propertyName,
        string $propertyDescription = '',
        string $format = self::FORMAT_INT64
    ) {
        parent::__construct($propertyName, $propertyDescription);

        $this->setFormat($format);
    }

    public function getType(): string
    {
        return 'integer';
    }

    public function getPhpType(): string
    {
        return 'int';
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        if (!\in_array($format, [self::FORMAT_INT32, self::FORMAT_INT64], true)) {
            throw new \InvalidArgumentException(\sprintf('Invalid format "%s"', $format));
        }

        $this->format = $format;

        return $this;
    }

    public function setMinimumValue(?int $minimumValue): self
    {
        $this->minimumValue = $minimumValue;

        return $this;
    }

    public function setMaximumValue(?int $maximumValue): self
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

    public function getMinimumValue(): ?int
    {
        return $this->minimumValue;
    }

    public function getMaximumValue(): ?int
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
