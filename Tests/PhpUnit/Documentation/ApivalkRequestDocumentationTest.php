<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use PHPUnit\Framework\TestCase;

class ApivalkRequestDocumentationTest extends TestCase
{
    private $requestDocumentation;

    protected function setUp(): void
    {
        $this->requestDocumentation = new ApivalkRequestDocumentation();
    }

    public function testAddAndGetBodyProperties(): void
    {
        $property = new StringProperty('testBody', 'Test Description');
        $this->requestDocumentation->addBodyProperty($property);

        $bodyProperties = $this->requestDocumentation->getBodyProperties();
        $this->assertCount(1, $bodyProperties);
        $this->assertArrayHasKey('testBody', $bodyProperties);
        $this->assertSame($property, $bodyProperties['testBody']);
    }

    public function testAddAndGetQueryProperties(): void
    {
        $property = new StringProperty('testQuery', 'Test Description');
        $this->requestDocumentation->addQueryProperty($property);

        $queryProperties = $this->requestDocumentation->getQueryProperties();
        $this->assertCount(1, $queryProperties);
        $this->assertArrayHasKey('testQuery', $queryProperties);
        $this->assertSame($property, $queryProperties['testQuery']);
    }

    public function testAddAndGetPathProperties(): void
    {
        $property = new StringProperty('testPath', 'Test Description');
        $this->requestDocumentation->addPathProperty($property);

        $pathProperties = $this->requestDocumentation->getPathProperties();
        $this->assertCount(1, $pathProperties);
        $this->assertArrayHasKey('testPath', $pathProperties);
        $this->assertSame($property, $pathProperties['testPath']);
    }

    public function testAddPaginationQueryProperties(): void
    {
        $this->requestDocumentation->addPaginationQueryProperties();

        $queryProperties = $this->requestDocumentation->getQueryProperties();
        $this->assertCount(1, $queryProperties);
        $this->assertArrayHasKey('page', $queryProperties);

        /** @var IntegerProperty $pageProperty */
        $pageProperty = $queryProperties['page'];
        $this->assertInstanceOf(IntegerProperty::class, $pageProperty);
        $this->assertSame('page', $pageProperty->getPropertyName());
        $this->assertFalse($pageProperty->isRequired());
        $this->assertSame(IntegerProperty::FORMAT_INT32, $pageProperty->getFormat());
    }
}
