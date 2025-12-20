<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

abstract class AbstractPropertyCollection implements \IteratorAggregate
{
    /** @var string */
    public const MODE_EDIT = 'edit';
    /** @var string */
    public const MODE_VIEW = 'view';
    /** @var string */
    public const MODE_CREATE = 'create';
    /** @var string */
    public const MODE_DELETE = 'delete';

    /** @var AbstractProperty[] */
    private $properties = [];

    abstract public function __construct(string $mode);

    public function addProperty(AbstractProperty $property): void
    {
        $this->properties[] = $property;
    }

    /**
     * @return \Iterator<int, AbstractProperty>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->properties);
    }
}
