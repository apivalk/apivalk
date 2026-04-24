<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

class DocBlockResource
{
    /** @var AbstractProperty[] */
    private $properties = [];

    public function addProperty(AbstractProperty $property): void
    {
        $this->properties[$property->getPropertyName()] = $property;
    }

    /** @return AbstractProperty[] */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getResourceDocBlock(): string
    {
        $lines = [];

        foreach ($this->properties as $property) {
            $type = $property->getPhpType();
            if (strpos($type, '\\') !== false && $type[0] !== '\\') {
                $type = '\\' . $type;
            }

            if (!$property->isRequired()) {
                $type = \sprintf('%s|null', $type);
            }

            $line = \sprintf('@property %s $%s', $type, $property->getPropertyName());

            $description = $property->getPropertyDescription();
            if ($description !== '') {
                $line .= ' ' . $description;
            }

            $lines[] = $line;
        }

        if (empty($lines)) {
            $body = ' * (no properties)';
        } else {
            $body = ' * ' . implode("\n * ", $lines);
        }

        return "/**\n" . $body . "\n */";
    }
}
