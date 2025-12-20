<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class ArrayProperty extends AbstractProperty
{
    /** @var AbstractObjectProperty */
    private $objectProperty;

    public function __construct(
        string $propertyName,
        string $propertyDescription = '',
        AbstractObjectProperty $objectProperty
    ) {
        parent::__construct($propertyName, $propertyDescription);

        $this->objectProperty = $objectProperty;
    }

    public function getType(): string
    {
        return 'array';
    }

    public function getPhpType(): string
    {
        return 'array';
    }

    public function getObjectProperty(): AbstractObjectProperty
    {
        return $this->objectProperty;
    }

    public function getDocumentationArray(): array
    {
        return [
            'type' => $this->getType(),
            'items' => $this->getObjectProperty()->getDocumentationArray(),
            'description' => $this->getPropertyDescription(),
        ];
    }
}
