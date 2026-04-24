<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Documentation;

use apivalk\apivalk\Documentation\Request\RequestDocumentationFactory;
use apivalk\apivalk\Documentation\Response\ResponseDocumentationFactory;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class RequestDocumentationFactoryTest extends TestCase
{
    public function testCreateResponseDocumentationViewMode(): void
    {
        $resource = new AnimalResource();
        $doc = ResponseDocumentationFactory::create(
            $resource,
            AbstractResource::MODE_VIEW
        );

        $propertyNames = array_map(function ($p) {
            return $p->getPropertyName();
        }, $doc->getProperties());

        // identifier + view properties (name, type, weight)
        $this->assertContains('animal_uuid', $propertyNames);
        $this->assertContains('name', $propertyNames);
        $this->assertContains('type', $propertyNames);
        $this->assertContains('weight', $propertyNames);
    }

    public function testCreateResponseDocumentationListMode(): void
    {
        $resource = new AnimalResource();
        $doc = ResponseDocumentationFactory::create(
            $resource,
            AbstractResource::MODE_LIST
        );

        $propertyNames = array_map(function ($p) {
            return $p->getPropertyName();
        }, $doc->getProperties());

        // weight is excluded from list mode
        $this->assertContains('animal_uuid', $propertyNames);
        $this->assertContains('name', $propertyNames);
        $this->assertContains('type', $propertyNames);
        $this->assertNotContains('weight', $propertyNames);
    }

    public function testCreateRequestDocumentationCreateMode(): void
    {
        $resource = new AnimalResource();
        $doc = RequestDocumentationFactory::createRequestDocumentation(
            $resource,
            AbstractResource::MODE_CREATE
        );

        $bodyPropertyNames = array_keys($doc->getBodyProperties());
        $pathPropertyNames = array_keys($doc->getPathProperties());

        $this->assertContains('name', $bodyPropertyNames);
        $this->assertContains('type', $bodyPropertyNames);
        $this->assertContains('weight', $bodyPropertyNames);
        $this->assertEmpty($pathPropertyNames);
    }

    public function testCreateRequestDocumentationUpdateMode(): void
    {
        $resource = new AnimalResource();
        $doc = RequestDocumentationFactory::createRequestDocumentation(
            $resource,
            AbstractResource::MODE_UPDATE
        );

        $bodyPropertyNames = array_keys($doc->getBodyProperties());
        $pathPropertyNames = array_keys($doc->getPathProperties());

        // All body properties present but not required
        $this->assertContains('name', $bodyPropertyNames);
        $this->assertContains('type', $bodyPropertyNames);
        $this->assertContains('weight', $bodyPropertyNames);

        foreach ($doc->getBodyProperties() as $property) {
            $this->assertFalse(
                $property->isRequired(),
                sprintf('Body property "%s" should be optional in update mode', $property->getPropertyName())
            );
        }

        // Identifier as path property
        $this->assertContains('animal_uuid', $pathPropertyNames);
    }

    public function testCreateRequestDocumentationViewMode(): void
    {
        $resource = new AnimalResource();
        $doc = RequestDocumentationFactory::createRequestDocumentation(
            $resource,
            AbstractResource::MODE_VIEW
        );

        $bodyPropertyNames = array_keys($doc->getBodyProperties());
        $pathPropertyNames = array_keys($doc->getPathProperties());

        $this->assertEmpty($bodyPropertyNames);
        $this->assertContains('animal_uuid', $pathPropertyNames);
    }

    public function testCreateRequestDocumentationDeleteMode(): void
    {
        $resource = new AnimalResource();
        $doc = RequestDocumentationFactory::createRequestDocumentation(
            $resource,
            AbstractResource::MODE_DELETE
        );

        $bodyPropertyNames = array_keys($doc->getBodyProperties());
        $pathPropertyNames = array_keys($doc->getPathProperties());

        $this->assertEmpty($bodyPropertyNames);
        $this->assertContains('animal_uuid', $pathPropertyNames);
    }

    public function testCreateRequestDocumentationListMode(): void
    {
        $resource = new AnimalResource();
        $doc = RequestDocumentationFactory::createRequestDocumentation(
            $resource,
            AbstractResource::MODE_LIST
        );

        $this->assertEmpty($doc->getBodyProperties());
        $this->assertEmpty($doc->getPathProperties());
    }

    public function testResponseDocumentationDescription(): void
    {
        $resource = new AnimalResource();
        $doc = ResponseDocumentationFactory::create(
            $resource,
            AbstractResource::MODE_VIEW
        );

        $this->assertSame('View animal response', $doc->getDescription());
    }
}
