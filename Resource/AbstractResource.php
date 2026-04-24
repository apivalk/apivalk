<?php

declare(strict_types=1);

namespace apivalk\apivalk\Resource;

use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\ParameterBagFactory;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Sort\Sort;

abstract class AbstractResource
{
    /** @var string */
    public const MODE_LIST = 'list';
    /** @var string */
    public const MODE_UPDATE = 'update';
    /** @var string */
    public const MODE_VIEW = 'view';
    /** @var string */
    public const MODE_CREATE = 'create';
    /** @var string */
    public const MODE_DELETE = 'delete';

    /** @var array<string, mixed> */
    private $data = [];
    /** @var AbstractProperty[] */
    private $properties = [];

    /**
     * Never overwrite __construct() in child classes.
     */
    final public function __construct()
    {
        $this->init();
    }

    abstract protected function init(): void;

    abstract public function getIdentifierProperty(): AbstractProperty;

    /** Returns base url for resource. Example: /api/v1 */
    abstract public function getBaseUrl(): string;

    abstract public function getName(): string;

    public function getPluralName(): string
    {
        return $this->getName() . 's';
    }

    /**
     * Filters this resource exposes for list endpoints. List controllers use this as the default
     * catalog; each controller may expose a subset via its own filters() hook.
     *
     * @return FilterInterface[]
     */
    public function availableFilters(): array
    {
        return [];
    }

    /**
     * Sortings this resource exposes for list endpoints. List controllers use this as the default
     * catalog; each controller may expose a subset via its own sortings() hook.
     *
     * @return Sort[]
     */
    public function availableSortings(): array
    {
        return [];
    }

    /**
     * OpenAPI tags that group operations for this resource. Controllers default to these; a
     * controller may override via its tags() hook.
     *
     * @return TagObject[]
     */
    public function tags(): array
    {
        return [];
    }

    /**
     * Return a property name array of properties that should be excluded from the given mode.
     *
     * Example:
     *
     * if ($mode === self::MODE_LIST) {
     *   return ['password'];
     * }
     *
     * In the list this property will not be returned or included in an array.
     *
     * @return string[]
     */
    abstract public function excludeFromMode(string $mode): array;

    protected function addProperty(AbstractProperty $property): void
    {
        $this->properties[$property->getPropertyName()] = $property;
    }

    protected function hasProperty(string $propertyName): bool
    {
        return \array_key_exists($propertyName, $this->properties);
    }

    /**
     * @return AbstractProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function byArray(array $data): self
    {
        $resource = new static();
        $identifierPropertyName = $resource->getIdentifierProperty()->getPropertyName();

        foreach ($data as $propertyName => $value) {
            if ($identifierPropertyName !== $propertyName
                && !$resource->hasProperty($propertyName)
            ) {
                continue;
            }

            if ($identifierPropertyName === $propertyName) {
                $resource->__set(
                    $propertyName,
                    ParameterBagFactory::typeCastValueByProperty($value, $resource->getIdentifierProperty())
                );
            } else {
                $resource->__set(
                    $propertyName,
                    ParameterBagFactory::typeCastValueByProperty($value, $resource->getProperties()[$propertyName])
                );
            }
        }

        return $resource;
    }

    public static function byRequest(ApivalkRequestInterface $apivalkRequest): self
    {
        $resource = new static();

        foreach ($apivalkRequest->body()->getIterator() as $bodyParameter) {
            if (!$resource->hasProperty($bodyParameter->getName())) {
                continue;
            }

            $resource->__set($bodyParameter->getName(), $bodyParameter->getValue());
        }

        $resourceIdentifierPropertyName = $resource->getIdentifierProperty()->getPropertyName();

        $pathIdentifier = $apivalkRequest->path()->get($resourceIdentifierPropertyName);
        if ($pathIdentifier !== null) {
            $resource->__set($resourceIdentifierPropertyName, $pathIdentifier->getValue());
        }

        return $resource;
    }

    /**
     * @param mixed $value
     */
    public function __set(string $propertyName, $value): void
    {
        $this->data[$propertyName] = $value;
    }

    public function has(string $propertyName): bool
    {
        return isset($this->data[$propertyName]);
    }

    /**
     * @return mixed
     */
    public function __get(string $propertyName)
    {
        return $this->data[$propertyName] ?? null;
    }

    /** @return array<string, mixed> */
    public function toArray(string $mode): array
    {
        $excluded = $this->excludeFromMode($mode);
        $data = [];

        foreach ($this->data as $propertyName => $value) {
            if (\in_array($propertyName, $excluded, true)) {
                continue;
            }

            $data[$propertyName] = $value;
        }

        return $data;
    }
}
