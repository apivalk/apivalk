<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

/**
 * Describes a one-dimensional array of scalar values (e.g. ["ids" => [23, 41, 22]]).
 *
 * Unlike {@see ArrayProperty}, which requires an object property to describe each
 * item, a simple array only needs the scalar item type. The configured type defines
 * what is written into the documentation as the array's item type.
 */
class SimpleArrayProperty extends AbstractProperty
{
    /** @var string */
    public const TYPE_INT = 'int';
    /** @var string */
    public const TYPE_STRING = 'string';
    /** @var string */
    public const TYPE_NUMBER = 'number';
    /** @var string */
    public const TYPE_BOOL = 'bool';

    /**
     * @var array<string, string>
     */
    private const SWAGGER_TYPE_MAP = [
        self::TYPE_INT => 'integer',
        self::TYPE_STRING => 'string',
        self::TYPE_NUMBER => 'number',
        self::TYPE_BOOL => 'boolean',
    ];

    /**
     * @var array<string, string>
     */
    private const PHP_TYPE_MAP = [
        self::TYPE_INT => 'int',
        self::TYPE_STRING => 'string',
        self::TYPE_NUMBER => 'float',
        self::TYPE_BOOL => 'bool',
    ];

    /** @var string */
    private $itemType;

    public function __construct(
        string $propertyName,
        string $propertyDescription = '',
        string $itemType = self::TYPE_STRING
    ) {
        parent::__construct($propertyName, $propertyDescription);

        $this->setItemType($itemType);
    }

    public function setItemType(string $itemType): self
    {
        if (!isset(self::SWAGGER_TYPE_MAP[$itemType])) {
            throw new \InvalidArgumentException(\sprintf('Invalid item type "%s"', $itemType));
        }

        $this->itemType = $itemType;

        return $this;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function getType(): string
    {
        return 'array';
    }

    public function getPhpType(): string
    {
        return self::PHP_TYPE_MAP[$this->itemType] . '[]';
    }

    /** @return array<string, mixed> */
    public function getDocumentationArray(): array
    {
        $array = [
            'type' => $this->getType(),
            'items' => [
                'type' => self::SWAGGER_TYPE_MAP[$this->itemType],
            ],
        ];

        if ($this->getPropertyDescription() !== '') {
            $array['description'] = $this->getPropertyDescription();
        }

        if ($this->getExample() !== null) {
            $array['example'] = $this->getExample();
        }

        return $array;
    }
}
