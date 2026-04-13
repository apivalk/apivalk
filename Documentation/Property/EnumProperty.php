<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class EnumProperty extends AbstractProperty
{
    /** @var string|null */
    private $default;
    /** @var array */
    private $enums;

    /**
     * @param string[] $enums array of valid values, for example ['active', 'inactive']
     */
    public function __construct(
        string $propertyName,
        string $propertyDescription,
        array $enums
    ) {
        parent::__construct($propertyName, $propertyDescription);

        $this->enums = $enums;
    }

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
