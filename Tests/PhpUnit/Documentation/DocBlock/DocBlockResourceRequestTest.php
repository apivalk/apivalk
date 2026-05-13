<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use apivalk\apivalk\Documentation\DocBlock\DocBlockResourceRequest;
use apivalk\apivalk\Documentation\DocBlock\DocBlockShape;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use PHPUnit\Framework\TestCase;

class DocBlockResourceRequestTest extends TestCase
{
    private function makePathShape(): DocBlockShape
    {
        $shape = new DocBlockShape('AnimalView', 'Path');
        $shape->addProperty(new StringProperty('animal_uuid', 'Animal UUID'));

        return $shape;
    }

    private function makeSortShape(): DocBlockShape
    {
        $shape = new DocBlockShape('AnimalList', 'Sorting');
        $shape->addCustomField('name', '\\' . \apivalk\apivalk\Router\Route\Sort\Sort::class);

        return $shape;
    }

    private function makeFilterShape(): DocBlockShape
    {
        $shape = new DocBlockShape('AnimalList', 'Filtering');
        $shape->addCustomField('status', 'string');

        return $shape;
    }

    private function emptyShape(string $name, string $type): DocBlockShape
    {
        return new DocBlockShape($name, $type);
    }

    public function testPathAnnotationEmittedWhenPathShapeHasProperties(): void
    {
        $docBlock = new DocBlockResourceRequest(
            $this->makePathShape(),
            $this->emptyShape('AnimalView', 'Sorting'),
            $this->emptyShape('AnimalView', 'Filtering'),
            null,
            ResourceRequest::class
        );
        $rendered = $docBlock->getDocBlockOnly('My\\Namespace\\Shape');

        self::assertStringContainsString('path()', $rendered);
        self::assertStringContainsString(ParameterBag::class, $rendered);
        self::assertStringNotContainsString('sorting()', $rendered);
        self::assertStringNotContainsString('filtering()', $rendered);
    }

    public function testPathAnnotationOmittedWhenPathShapeIsEmpty(): void
    {
        $docBlock = new DocBlockResourceRequest(
            $this->emptyShape('AnimalList', 'Path'),
            $this->makeSortShape(),
            $this->makeFilterShape(),
            null,
            ResourceRequest::class
        );
        $rendered = $docBlock->getDocBlockOnly('My\\Namespace\\Shape');

        self::assertStringNotContainsString('path()', $rendered);
        self::assertStringContainsString('sorting()', $rendered);
        self::assertStringContainsString('filtering()', $rendered);
    }

    public function testSortingAndFilteringAnnotationsEmitted(): void
    {
        $docBlock = new DocBlockResourceRequest(
            $this->emptyShape('AnimalList', 'Path'),
            $this->makeSortShape(),
            $this->makeFilterShape(),
            null,
            ResourceRequest::class
        );
        $rendered = $docBlock->getDocBlockOnly('My\\Namespace\\Shape');

        self::assertStringContainsString('sorting()', $rendered);
        self::assertStringContainsString('filtering()', $rendered);
        self::assertStringNotContainsString('paginator()', $rendered);
    }

    public function testPaginatorAnnotationEmittedWhenSet(): void
    {
        $docBlock = new DocBlockResourceRequest(
            $this->emptyShape('AnimalList', 'Path'),
            $this->makeSortShape(),
            $this->makeFilterShape(),
            PagePaginator::class,
            ResourceRequest::class
        );
        $rendered = $docBlock->getDocBlockOnly('My\\Namespace\\Shape');

        self::assertStringContainsString('paginator()', $rendered);
        self::assertStringContainsString(PagePaginator::class, $rendered);
    }

    public function testDocBlockUsesClassConstantsNotHardcodedStrings(): void
    {
        $docBlock = new DocBlockResourceRequest(
            $this->makePathShape(),
            $this->makeSortShape(),
            $this->makeFilterShape(),
            null,
            ResourceRequest::class
        );
        $rendered = $docBlock->getDocBlockOnly('My\\Namespace\\Shape');

        self::assertStringContainsString(ParameterBag::class, $rendered);
        self::assertStringContainsString(SortBag::class, $rendered);
        self::assertStringContainsString(FilterBag::class, $rendered);
    }

    public function testShapeNamespace(): void
    {
        $docBlock = new DocBlockResourceRequest(
            $this->emptyShape('AnimalView', 'Path'),
            $this->emptyShape('AnimalView', 'Sorting'),
            $this->emptyShape('AnimalView', 'Filtering'),
            null,
            ResourceRequest::class
        );

        self::assertSame('My\\NS\\Shape', $docBlock->getShapeNamespace('My\\NS'));
    }

    public function testGetShapeFilenamesAlwaysReturnsAllThreeKeys(): void
    {
        $docBlock = new DocBlockResourceRequest(
            $this->emptyShape('AnimalCreate', 'Path'),
            $this->emptyShape('AnimalCreate', 'Sorting'),
            $this->emptyShape('AnimalCreate', 'Filtering'),
            null,
            ResourceRequest::class
        );
        $filenames = $docBlock->getShapeFilenames('/var/app/Request');

        self::assertArrayHasKey('path', $filenames);
        self::assertArrayHasKey('sorting', $filenames);
        self::assertArrayHasKey('filtering', $filenames);
        self::assertStringContainsString('AnimalCreatePathShape', $filenames['path']);
        self::assertStringContainsString('AnimalCreateSortingShape', $filenames['sorting']);
        self::assertStringContainsString('AnimalCreateFilteringShape', $filenames['filtering']);
    }
}
