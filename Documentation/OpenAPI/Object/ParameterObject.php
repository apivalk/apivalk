<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\Operator;

/**
 * Class ParameterObject
 *
 * @see https://swagger.io/specification/#parameter-object
 */
class ParameterObject implements ObjectInterface
{
    private string $name;

    private string $in;

    private string $description;

    private bool $required;

    private AbstractProperty $property;

    private ?string $style = null;

    private ?bool $explode = null;

    /** @var array<string, mixed>|null */
    private ?array $rawSchema = null;

    public function __construct(string $in, AbstractProperty $property)
    {
        $this->name        = $property->getPropertyName();
        $this->in          = $in;
        $this->description = $property->getPropertyDescription();
        $this->required    = $property->isRequired();
        $this->property    = $property;
        $this->style       = null;
        $this->explode     = null;
        $this->rawSchema   = null;
    }

    /**
     * One deepObject parameter per filter field, whose properties are the operators the
     * field allows: `?price[gt]=10&price[lt]=100`.
     *
     * A single bracket level with primitive properties is the case OpenAPI actually
     * defines for deepObject, which is why filters are not nested under a `filter` key.
     */
    public static function forFilter(FilterInterface $filter): self
    {
        $property = $filter->getProperty();

        $instance = new self('query', $property);
        $instance->required = false;
        $instance->style = 'deepObject';
        $instance->explode = true;

        $operatorSchemas = [];
        foreach ($filter->getAllowedOperators() as $operator) {
            $operatorSchemas[$operator] = self::operatorSchema($operator, $property);
        }

        $instance->rawSchema = [
            'type' => 'object',
            'properties' => $operatorSchemas,
            'additionalProperties' => false,
        ];

        return $instance;
    }

    /**
     * @return array<string, mixed>
     */
    private static function operatorSchema(string $operator, AbstractProperty $property): array
    {
        if ($operator === Operator::NULL) {
            return [
                'type' => 'boolean',
                'description' => 'true matches null values, false matches non-null values.',
            ];
        }

        if ($operator === Operator::IN) {
            return [
                'type' => 'string',
                'description' => 'Comma-separated list of values.',
            ];
        }

        return $property->getDocumentationArray();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIn(): string
    {
        return $this->in;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getProperty(): ?AbstractProperty
    {
        return $this->property;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function toArray(): array
    {
        $schema = $this->rawSchema ?? $this->property->getDocumentationArray();

        return array_filter(
            [
                'name'        => $this->name,
                'in'          => $this->in,
                'description' => $this->description,
                'required'    => $this->required,
                'style'       => $this->style,
                'explode'     => $this->explode,
                'schema'      => $schema,
            ],
            static fn($value) => $value !== null
        );
    }
}
