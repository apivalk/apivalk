<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

class DocBlockShape
{
    /** @var AbstractProperty[] */
    private $properties = [];
    /** @var string */
    private $type;
    /** @var string */
    private $requestName;

    public function __construct(string $requestName, string $type)
    {
        $this->requestName = $requestName;
        $this->type = $type;
    }

    public function addProperty(AbstractProperty $property): void
    {
        $this->properties[] = $property;
    }

    public function getClassName(): string
    {
        $className = \sprintf('%s%sShape', \ucfirst($this->requestName), $this->type);

        if (!preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $className)) {
            throw new \InvalidArgumentException('Invalid class name: ' . $className);
        }

        return $className;
    }

    public function toString(string $namespace): string
    {
        $namespaceParts = explode('\\', $namespace);
        foreach ($namespaceParts as $part) {
            if (!preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $part)) {
                throw new \InvalidArgumentException(\sprintf('Invalid namespace: %s', $namespace));
            }
        }

        $string = <<<'PHP'
<?php

declare(strict_types=1);

namespace {{NAMESPACE}};

/**
{{PROPERTIES}}
 */
interface {{CLASS_NAME}}
{
}
PHP;

        $propertiesString = [];

        foreach ($this->properties as $property) {
            $type = $property->getPhpType();
            if (strpos($type, '\\') !== false && $type[0] !== '\\') {
                $type = '\\' . $type;
            }

            $propertiesString[] = \sprintf(
                '@property-read %s $%s',
                $property->isRequired()
                    ? $type
                    : \sprintf('%s|null', $type),
                $property->getPropertyName()
            );
        }

        if (empty($propertiesString)) {
            $propertiesBlock = ' * (empty shape)';
        } else {
            $propertiesBlock = ' * ' . implode("\n * ", $propertiesString);
        }

        return str_replace(
            ['{{NAMESPACE}}', '{{CLASS_NAME}}', '{{PROPERTIES}}'],
            [$namespace, $this->getClassName(), $propertiesBlock],
            $string
        );
    }
}
