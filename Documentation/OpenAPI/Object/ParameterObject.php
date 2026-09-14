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
    /**
     * How a single-operator filter matches, stated for readers of the flat form.
     *
     * @var array<string, string>
     */
    private const SINGLE_OPERATOR_HINTS = [
        Operator::EQ => 'Equals filtering on `%s`.',
        Operator::NEQ => 'Not-equals filtering on `%s`.',
        Operator::IN => 'List filtering on `%s`. Matches any of the listed values.',
        Operator::GT => 'Greater-than filtering on `%s`.',
        Operator::GTE => 'Greater-than-or-equal filtering on `%s`.',
        Operator::LT => 'Less-than filtering on `%s`.',
        Operator::LTE => 'Less-than-or-equal filtering on `%s`.',
        Operator::LIKE => 'Pattern-match filtering on `%s`.',
        Operator::CONTAINS => 'Substring filtering on `%s`.',
        Operator::NULL => 'Null-check filtering on `%s`. true matches null values, false matches non-null values.',
    ];

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
     * A field with exactly one operator has nothing to choose, so it is documented flat:
     * `?status=active`. Bracket notation for a single operator is noise a reader has to
     * decode for no gain, and flat notation is what the population strategy resolves it to.
     *
     * A field with several operators becomes one deepObject parameter whose properties are
     * the operators it allows: `?price[gt]=10&price[lt]=100`. A single bracket level with
     * primitive properties is the case OpenAPI actually defines for deepObject, which is why
     * filters are not nested under a `filter` key.
     */
    public static function forFilter(FilterInterface $filter): self
    {
        $property = $filter->getProperty();
        $operators = $filter->getAllowedOperators();

        $instance = new self('query', $property);
        $instance->required = false;

        if (\count($operators) === 1) {
            $instance->description = self::singleOperatorDescription($operators[0], $property);

            if ($operators[0] === Operator::IN) {
                // A list of values is an array, not a string that happens to hold commas.
                // `form` without `explode` is the style that serialises it as `?status=a,b`,
                // and it is the only spelling that keeps the item constraints documented.
                $instance->style = 'form';
                $instance->explode = false;
                $instance->rawSchema = [
                    'type' => 'array',
                    'items' => self::valueSchema($property),
                ];

                return $instance;
            }

            $instance->rawSchema = self::operatorSchema($operators[0], $property);

            return $instance;
        }

        $instance->style = 'deepObject';
        $instance->explode = true;

        $operatorSchemas = [];
        foreach ($operators as $operator) {
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
     * The operator disappears from the wire format when a field declares only one, so it
     * has to be stated in prose instead: the parameter alone no longer says how it matches.
     */
    private static function singleOperatorDescription(string $operator, AbstractProperty $property): string
    {
        if (!isset(self::SINGLE_OPERATOR_HINTS[$operator])) {
            throw new \InvalidArgumentException(\sprintf(
                'Filter operator "%s" on field "%s" has no documentation hint. Add it to %s::SINGLE_OPERATOR_HINTS.',
                $operator,
                $property->getPropertyName(),
                self::class
            ));
        }

        $hint = \sprintf(self::SINGLE_OPERATOR_HINTS[$operator], $property->getPropertyName());
        $description = $property->getPropertyDescription();

        return $description === '' ? $hint : $hint . ' ' . $description;
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

        // deepObject is defined for one bracket level of primitive properties, so a nested
        // `in` stays a comma-separated string. Only the flat form can spell it as an array.
        if ($operator === Operator::IN) {
            return [
                'type' => 'string',
                'description' => 'Comma-separated list of values.',
            ];
        }

        return self::valueSchema($property);
    }

    /**
     * @return array<string, mixed>
     */
    private static function valueSchema(AbstractProperty $property): array
    {
        $schema = $property->getDocumentationArray();

        // A property default describes the resource field, not the filter. On a parameter
        // schema OpenAPI reads it as "omitting this applies that value", which is the
        // opposite of what the population strategy does: an absent filter adds no condition.
        unset($schema['default']);

        return $schema;
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
