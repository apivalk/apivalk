<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use apivalk\apivalk\Documentation\OpenAPI\Object\ParameterObject;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
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
}
