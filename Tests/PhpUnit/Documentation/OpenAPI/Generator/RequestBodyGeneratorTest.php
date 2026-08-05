<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\RequestBodyGenerator;
use apivalk\apivalk\Documentation\Property\FileProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class RequestBodyGeneratorTest extends TestCase
{
    public function testRequestBodyGenerator(): void
    {
        $generator = new RequestBodyGenerator();
        $doc = $this->createMock(ApivalkRequestDocumentation::class);
        $route = $this->createMock(Route::class);
        $route->method('getDescription')->willReturn('Description');

        $requestBody = $generator->generate($doc, $route);

        $this->assertEquals('Description', $requestBody->getDescription());
    }

    public function testBodyWithoutFilePropertiesStaysJson(): void
    {
        $documentation = new ApivalkRequestDocumentation();
        $documentation->addBodyProperty(new StringProperty('name', 'A name'));

        $requestBody = (new RequestBodyGenerator())->generate($documentation, $this->createMock(Route::class));

        $this->assertEquals(
            RequestBodyGenerator::MEDIA_TYPE_JSON,
            $requestBody->getContent()->getMediaType()
        );
    }

    public function testDeclaredFilePropertiesTurnTheBodyIntoMultipart(): void
    {
        $documentation = new ApivalkRequestDocumentation();
        $documentation->addBodyProperty(new StringProperty('document_type', 'The document type'));
        $documentation->addFileProperty(new FileProperty('file', 'The uploaded document'));

        $requestBody = (new RequestBodyGenerator())->generate($documentation, $this->createMock(Route::class));

        $this->assertEquals(
            RequestBodyGenerator::MEDIA_TYPE_MULTIPART,
            $requestBody->getContent()->getMediaType()
        );
    }

    /**
     * A request may consist of nothing but an upload, so the media type must not depend on body properties.
     */
    public function testFileOnlyRequestIsStillMultipart(): void
    {
        $documentation = new ApivalkRequestDocumentation();
        $documentation->addFileProperty(new FileProperty('file', 'The uploaded document'));

        $requestBody = (new RequestBodyGenerator())->generate($documentation, $this->createMock(Route::class));
        $schema = $requestBody->getContent()->getSchema()->toArray();

        $this->assertEquals(
            RequestBodyGenerator::MEDIA_TYPE_MULTIPART,
            $requestBody->getContent()->getMediaType()
        );
        $this->assertSame(['file'], array_keys($schema['properties']));
    }

    public function testMultipartSchemaCarriesBodyAndFileProperties(): void
    {
        $documentation = new ApivalkRequestDocumentation();
        $documentation->addBodyProperty(new StringProperty('document_type', 'The document type'));
        $documentation->addFileProperty(new FileProperty('file', 'The uploaded document'));

        $requestBody = (new RequestBodyGenerator())->generate($documentation, $this->createMock(Route::class));
        $schema = $requestBody->getContent()->getSchema()->toArray();

        $this->assertArrayHasKey('document_type', $schema['properties']);
        $this->assertArrayHasKey('file', $schema['properties']);
        $this->assertEquals('binary', $schema['properties']['file']['format']);
        $this->assertContains('file', $schema['required']);
    }
}
