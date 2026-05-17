<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\OpenAPI\Object\ParameterObject;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use PHPUnit\Framework\TestCase;

class TestProperty extends AbstractProperty
{
    /** @var array<string, mixed> */
    private $documentationArray;

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

    public function testForFilterGroupProducesDeepObjectParameter(): void
    {
        $statusProp = new StringProperty('status', 'Filter by status');
        $statusProp->setIsRequired(false);

        $idProp = new IntegerProperty('customer_id', 'Filter by customer ID');
        $idProp->setIsRequired(false);

        $parameter = ParameterObject::forFilterGroup([$statusProp, $idProp]);

        $this->assertEquals('filter', $parameter->getName());
        $this->assertEquals('query', $parameter->getIn());
        $this->assertFalse($parameter->isRequired());
        $this->assertEquals('deepObject', $parameter->getStyle());

        $array = $parameter->toArray();
        $this->assertEquals('deepObject', $array['style']);
        $this->assertTrue($array['explode']);
        $this->assertEquals('object', $array['schema']['type']);
        $this->assertArrayHasKey('status', $array['schema']['properties']);
        $this->assertArrayHasKey('customer_id', $array['schema']['properties']);
        $this->assertEquals('string', $array['schema']['properties']['status']['type']);
        $this->assertEquals('integer', $array['schema']['properties']['customer_id']['type']);
    }

    public function testForFilterGroupDescriptionMentionsBracketNotation(): void
    {
        $prop = new StringProperty('status', 'Filter by status');
        $prop->setIsRequired(false);

        $parameter = ParameterObject::forFilterGroup([$prop]);

        $this->assertStringContainsString('filter[', $parameter->getDescription() ?? '');
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
