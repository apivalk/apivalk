<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Router\Route\Filter\EnumFilter;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Filter\Operator;
use apivalk\apivalk\Documentation\OpenAPI\Object\ParameterObject;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use PHPUnit\Framework\TestCase;

class TestProperty extends AbstractProperty
{
    /** @var array<string, mixed> */
    private array $documentationArray;

    /**
     * @param array<string, mixed> $documentationArray
     */
    public function __construct(
        string $propertyName,
        ?string $propertyDescription,
        bool $required,
        array $documentationArray
    ) {
        parent::__construct($propertyName, $propertyDescription);
        $this->isRequired = $required;
        $this->documentationArray = $documentationArray;
    }

    public function getDocumentationArray(): array
    {
        return $this->documentationArray;
    }

    public function getType(): string
    {
        return 'string';
    }

    public function getPhpType(): string
    {
        return 'string';
    }
}

class ParameterObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $property = new TestProperty(
            'id',
            'User ID',
            true,
            [
                'type' => 'integer',
                'required' => ['id'],
            ]
        );

        $parameter = new ParameterObject('path', $property);

        $expected = [
            'name' => 'id',
            'in' => 'path',
            'description' => 'User ID',
            'required' => true,
            'schema' => [
                'type' => 'integer',
                'required' => ['id'],
            ],
        ];

        $this->assertEquals($expected, $parameter->toArray());
    }

    public function testToArrayMinimal(): void
    {
        $property = new TestProperty(
            'id',
            '',
            true,
            [
                'type' => 'string',
            ]
        );

        $parameter = new ParameterObject('query', $property);

        $expected = [
            'name' => 'id',
            'in' => 'query',
            'description' => '',
            'required' => true,
            'schema' => [
                'type' => 'string',
            ],
        ];

        $this->assertEquals($expected, $parameter->toArray());
    }
    public function testForFilterProducesADeepObjectPerField(): void
    {
        $filter = new IntegerFilter(new IntegerProperty('price', 'Filter by price'), Operator::GT, Operator::LTE);

        $parameter = ParameterObject::forFilter($filter);

        $this->assertEquals('price', $parameter->getName());
        $this->assertEquals('query', $parameter->getIn());
        $this->assertFalse($parameter->isRequired());
        $this->assertEquals('deepObject', $parameter->getStyle());

        $array = $parameter->toArray();
        $this->assertEquals('deepObject', $array['style']);
        $this->assertTrue($array['explode']);
        $this->assertEquals('object', $array['schema']['type']);
        $this->assertFalse($array['schema']['additionalProperties']);
        $this->assertSame(['gt', 'lte'], array_keys($array['schema']['properties']));
        $this->assertEquals('integer', $array['schema']['properties']['gt']['type']);
    }

    public function testForFilterDocumentsASingleOperatorFieldFlat(): void
    {
        $filter = new IntegerFilter(new IntegerProperty('price', 'Filter by price'), Operator::GT);

        $parameter = ParameterObject::forFilter($filter);

        $this->assertEquals('price', $parameter->getName());
        $this->assertEquals('query', $parameter->getIn());
        $this->assertFalse($parameter->isRequired());
        $this->assertNull($parameter->getStyle());
        $this->assertEquals('Greater-than filtering on `price`. Filter by price', $parameter->getDescription());

        $array = $parameter->toArray();
        $this->assertArrayNotHasKey('style', $array);
        $this->assertArrayNotHasKey('explode', $array);
        $this->assertEquals('integer', $array['schema']['type']);
        $this->assertArrayNotHasKey('properties', $array['schema']);
    }

    public function testForFilterStatesTheOperatorWithoutAPropertyDescription(): void
    {
        $filter = new StringFilter(new StringProperty('status'), Operator::EQ);

        $this->assertEquals(
            'Equals filtering on `status`.',
            ParameterObject::forFilter($filter)->getDescription()
        );
    }

    /**
     * A comma-separated list is an array in OpenAPI terms. Spelling it as one is what keeps
     * the item constraints, an enum above all, in the document.
     */
    public function testForFilterDocumentsASingleInOperatorAsAnArray(): void
    {
        $parameter = ParameterObject::forFilter(
            new EnumFilter(new EnumProperty('status', 'Status', ['draft', 'active']), Operator::IN)
        );

        $array = $parameter->toArray();

        $this->assertEquals('form', $array['style']);
        $this->assertFalse($array['explode']);
        $this->assertEquals('array', $array['schema']['type']);
        $this->assertEquals('string', $array['schema']['items']['type']);
        $this->assertSame(['draft', 'active'], $array['schema']['items']['enum']);
        $this->assertStringContainsString('List filtering on `status`.', $parameter->getDescription());
    }

    public function testForFilterKeepsTheOperatorSchemaForASingleNullOperator(): void
    {
        $null = ParameterObject::forFilter(
            new IntegerFilter(new IntegerProperty('price', 'Filter by price'), Operator::NULL)
        );

        $this->assertEquals('boolean', $null->toArray()['schema']['type']);
        $this->assertStringContainsString('true matches null values', $null->getDescription());
    }

    /**
     * deepObject is defined for one bracket level of primitive properties, so a nested `in`
     * cannot be an array the way the flat form can.
     */
    public function testForFilterKeepsANestedInOperatorAString(): void
    {
        $properties = ParameterObject::forFilter(
            new EnumFilter(new EnumProperty('status', 'Status', ['draft', 'active']), Operator::IN, Operator::EQ)
        )->toArray()['schema']['properties'];

        $this->assertEquals('string', $properties['in']['type']);
        $this->assertArrayNotHasKey('items', $properties['in']);
    }

    public function testForFilterDocumentsInAsACommaSeparatedStringAndNullAsBoolean(): void
    {
        $filter = new IntegerFilter(new IntegerProperty('price', 'Filter by price'), Operator::IN, Operator::NULL);

        $properties = ParameterObject::forFilter($filter)->toArray()['schema']['properties'];

        $this->assertEquals('string', $properties['in']['type']);
        $this->assertStringContainsString('Comma-separated', $properties['in']['description']);
        $this->assertEquals('boolean', $properties['null']['type']);
    }

    public function testRegularParameterHasNoStyleOrExplode(): void
    {
        $property = new TestProperty('id', 'ID', true, ['type' => 'string']);
        $parameter = new ParameterObject('path', $property);

        $array = $parameter->toArray();
        $this->assertArrayNotHasKey('style', $array);
        $this->assertArrayNotHasKey('explode', $array);
    }
}
