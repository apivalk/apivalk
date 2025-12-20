<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\DocBlock\DocBlockRequestGenerator;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Documentation\Property\NumberProperty;

class TestRequest extends AbstractApivalkRequest {
    public static function getDocumentation(): ApivalkRequestDocumentation {
        $doc = new ApivalkRequestDocumentation();
        $doc->addBodyProperty(new StringProperty('name'));
        $doc->addQueryProperty(new NumberProperty('id'));
        $doc->addPathProperty(new StringProperty('slug'));
        return $doc;
    }
}

class DocBlockRequestGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $generator = new DocBlockRequestGenerator();
        $request = new TestRequest();

        $docBlockRequest = $generator->generate($request);

        $this->assertEquals('TestRequestBodyShape', $docBlockRequest->getBodyShape()->getClassName());
        $this->assertEquals('TestRequestPathShape', $docBlockRequest->getPathShape()->getClassName());
        $this->assertEquals('TestRequestQueryShape', $docBlockRequest->getQueryShape()->getClassName());

        $bodyString = $docBlockRequest->getBodyShape()->toString('App\\Shape');
        $this->assertStringContainsString('@property-read string $name', $bodyString);

        $queryString = $docBlockRequest->getQueryShape()->toString('App\\Shape');
        $this->assertStringContainsString('@property-read float $id', $queryString);

        $pathString = $docBlockRequest->getPathShape()->toString('App\\Shape');
        $this->assertStringContainsString('@property-read string $slug', $pathString);
    }
}
