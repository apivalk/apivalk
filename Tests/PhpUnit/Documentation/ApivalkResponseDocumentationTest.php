<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\Property\StringProperty;
use PHPUnit\Framework\TestCase;

class ApivalkResponseDocumentationTest extends TestCase
{
    private $responseDocumentation;

    protected function setUp(): void
    {
        $this->responseDocumentation = new ApivalkResponseDocumentation();
    }

    public function testAddAndGetProperties(): void
    {
        $property = new StringProperty('testProperty', 'Test Description');
        $this->responseDocumentation->addProperty($property);

        $properties = $this->responseDocumentation->getProperties();
        $this->assertCount(1, $properties);
        $this->assertSame($property, $properties[0]);
    }

    public function testSetAndGetDescription(): void
    {
        $description = 'This is a test description';
        $this->responseDocumentation->setDescription($description);

        $this->assertSame($description, $this->responseDocumentation->getDescription());
    }

    public function testSetAndGetResponsePagination(): void
    {
        $this->assertFalse($this->responseDocumentation->hasResponsePagination());

        $this->responseDocumentation->setHasResponsePagination(true);
        $this->assertTrue($this->responseDocumentation->hasResponsePagination());

        $this->responseDocumentation->setHasResponsePagination(false);
        $this->assertFalse($this->responseDocumentation->hasResponsePagination());
    }
}
