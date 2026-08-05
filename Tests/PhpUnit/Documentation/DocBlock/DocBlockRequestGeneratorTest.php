<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use apivalk\apivalk\Documentation\Property\FileProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\DocBlock\DocBlockRequestGenerator;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;

class TestRequest extends AbstractApivalkRequest {
    public static function getDocumentation(): ApivalkRequestDocumentation {
        $doc = new ApivalkRequestDocumentation();
        $doc->addBodyProperty(new StringProperty('name'));
        $doc->addQueryProperty(new IntegerProperty('id'));
        $doc->addPathProperty(new StringProperty('slug'));
        return $doc;
    }
}

class TestUploadRequest extends AbstractApivalkRequest {
    public static function getDocumentation(): ApivalkRequestDocumentation {
        $doc = new ApivalkRequestDocumentation();
        $doc->addBodyProperty(new StringProperty('document_type'));
        $doc->addFileProperty(new FileProperty('file', 'The document'));
        $doc->addFileProperty((new FileProperty('preview', 'Optional preview'))->setIsRequired(false));
        return $doc;
    }
}

class DocBlockRequestGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $generator = new DocBlockRequestGenerator();
        $request = new TestRequest();
        $route = Route::get('test');

        $docBlockRequest = $generator->generate($request, $route);

        $this->assertEquals('TestRequestBodyShape', $docBlockRequest->getBodyShape()->getClassName());
        $this->assertEquals('TestRequestPathShape', $docBlockRequest->getPathShape()->getClassName());
        $this->assertEquals('TestRequestQueryShape', $docBlockRequest->getQueryShape()->getClassName());
        $this->assertEquals('TestRequestSortingShape', $docBlockRequest->getSortingShape()->getClassName());

        $bodyString = $docBlockRequest->getBodyShape()->toString('App\\Shape');
        $this->assertStringContainsString('@property-read string $name', $bodyString);

        $queryString = $docBlockRequest->getQueryShape()->toString('App\\Shape');
        $this->assertStringContainsString('@property-read int $id', $queryString);

        $pathString = $docBlockRequest->getPathShape()->toString('App\\Shape');
        $this->assertStringContainsString('@property-read string $slug', $pathString);

        $orderingString = $docBlockRequest->getSortingShape()->toString('App\\Shape');
    }

    public function testRequestWithoutFilePropertiesProducesNoFileShape(): void
    {
        $docBlockRequest = (new DocBlockRequestGenerator())->generate(new TestRequest(), Route::get('test'));

        $this->assertFalse($docBlockRequest->hasFileShape());
    }

    /**
     * The file bag hands out File objects, so the shape must not fall back to the property's PHP type.
     */
    public function testFilePropertiesLandInTheFileShapeTypedAsFile(): void
    {
        $docBlockRequest = (new DocBlockRequestGenerator())->generate(new TestUploadRequest(), Route::post('upload'));

        $this->assertTrue($docBlockRequest->hasFileShape());
        $this->assertEquals('TestUploadRequestFileShape', $docBlockRequest->getFileShape()->getClassName());

        $fileString = $docBlockRequest->getFileShape()->toString('App\\Shape');
        $this->assertStringContainsString(
            '@property-read \apivalk\apivalk\Http\Request\File\File $file',
            $fileString
        );
        $this->assertStringContainsString(
            '@property-read \apivalk\apivalk\Http\Request\File\File|null $preview',
            $fileString
        );

        // Uploads must not leak into the body shape, they are populated from a different source.
        $this->assertStringNotContainsString('$file', $docBlockRequest->getBodyShape()->toString('App\\Shape'));
    }

    public function testRoutePathPropertyAppearsInPathShape(): void
    {
        $generator = new DocBlockRequestGenerator();
        $request = new TestRequest();
        $route = Route::get('/items/{item_id}')->pathProperty(new IntegerProperty('item_id', 'Item ID'));

        $docBlockRequest = $generator->generate($request, $route);
        $pathString = $docBlockRequest->getPathShape()->toString('App\\Shape');

        $this->assertStringContainsString('@property-read int $item_id', $pathString);
    }

    public function testRoutePathPropertyDoesNotDuplicateRequestClassProperty(): void
    {
        $generator = new DocBlockRequestGenerator();
        $request = new TestRequest();
        $route = Route::get('/items/{slug}')->pathProperty(new StringProperty('extra', 'Extra'));

        $docBlockRequest = $generator->generate($request, $route);
        $pathString = $docBlockRequest->getPathShape()->toString('App\\Shape');

        $this->assertStringContainsString('@property-read string $slug', $pathString);
        $this->assertStringContainsString('@property-read string $extra', $pathString);
    }
}
