<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\FileProperty;
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

    public function testAddAndGetFileProperties(): void
    {
        $property = new FileProperty('file', 'Test Description');
        $this->requestDocumentation->addFileProperty($property);

        $fileProperties = $this->requestDocumentation->getFileProperties();
        $this->assertCount(1, $fileProperties);
        $this->assertArrayHasKey('file', $fileProperties);
        $this->assertSame($property, $fileProperties['file']);
    }

    public function testFilePropertiesAreKeptApartFromBodyProperties(): void
    {
        $this->requestDocumentation->addFileProperty(new FileProperty('file'));

        $this->assertCount(0, $this->requestDocumentation->getBodyProperties());
        $this->assertCount(1, $this->requestDocumentation->getFileProperties());
    }
}
