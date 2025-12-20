<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

abstract class AbstractObjectProperty extends AbstractProperty
{
    abstract public function getPropertyCollection(): AbstractPropertyCollection;

    final public function getType(): string
    {
        return 'object';
    }

    final public function getPhpType(): string
    {
        return static::class;
    }

    final public function getDocumentationArray(): array
    {
        $array = [
            'type' => $this->getType(),
            'properties' => [],
        ];

        $required = [];

        foreach ($this->getPropertyCollection() as $property) {
            $array['properties'][$property->getPropertyName()] = $property->getDocumentationArray();

            if ($property->isRequired()) {
                $required[] = $property->getPropertyName();
            }
        }

        if (!empty($required)) {
            $array['required'] = $required;
        }

        if ($this->getPropertyDescription() !== '') {
            $array['description'] = $this->getPropertyDescription();
        }

        return $array;
    }
}
