<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use apivalk\apivalk\Documentation\DocBlock\DocBlockResourceRequest;
use apivalk\apivalk\Documentation\DocBlock\DocBlockShape;
use apivalk\apivalk\Http\Request\Pagination\PagePaginator;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use PHPUnit\Framework\TestCase;

class DocBlockResourceRequestTest extends TestCase
{
    public function testGetDocBlockOnlyContainsSortingAndFiltering(): void
    {
        $sortShape = new DocBlockShape('AnimalList', 'Sorting');
        $filterShape = new DocBlockShape('AnimalList', 'Filtering');

        $docBlock = new DocBlockResourceRequest($sortShape, $filterShape, null, ResourceRequest::class);
        $rendered = $docBlock->getDocBlockOnly('My\\Namespace\\Shape');

        self::assertStringContainsString('sorting()', $rendered);
        self::assertStringContainsString('filtering()', $rendered);
        self::assertStringNotContainsString('paginator()', $rendered);
    }

    public function testGetDocBlockOnlyContainsPaginatorWhenSet(): void
    {
        $sortShape = new DocBlockShape('AnimalList', 'Sorting');
        $filterShape = new DocBlockShape('AnimalList', 'Filtering');

        $docBlock = new DocBlockResourceRequest($sortShape, $filterShape, PagePaginator::class, ResourceRequest::class);
        $rendered = $docBlock->getDocBlockOnly('My\\Namespace\\Shape');

        self::assertStringContainsString('paginator()', $rendered);
        self::assertStringContainsString(PagePaginator::class, $rendered);
    }

    public function testGetDocBlockUsesClassConstantsNotHardcodedStrings(): void
    {
        $sortShape = new DocBlockShape('AnimalList', 'Sorting');
        $filterShape = new DocBlockShape('AnimalList', 'Filtering');

        $docBlock = new DocBlockResourceRequest($sortShape, $filterShape, null, ResourceRequest::class);
        $rendered = $docBlock->getDocBlockOnly('My\\Namespace\\Shape');

        self::assertStringContainsString(SortBag::class, $rendered);
        self::assertStringContainsString(FilterBag::class, $rendered);
    }

    public function testShapeNamespace(): void
    {
        $sortShape = new DocBlockShape('AnimalList', 'Sorting');
        $filterShape = new DocBlockShape('AnimalList', 'Filtering');

        $docBlock = new DocBlockResourceRequest($sortShape, $filterShape, null, ResourceRequest::class);

        self::assertSame('My\\NS\\Shape', $docBlock->getShapeNamespace('My\\NS'));
    }

    public function testGetShapeFilenames(): void
    {
        $sortShape = new DocBlockShape('AnimalList', 'Sorting');
        $filterShape = new DocBlockShape('AnimalList', 'Filtering');

        $docBlock = new DocBlockResourceRequest($sortShape, $filterShape, null, ResourceRequest::class);
        $filenames = $docBlock->getShapeFilenames('/var/app/Request');

        self::assertArrayHasKey('sorting', $filenames);
        self::assertArrayHasKey('filtering', $filenames);
        self::assertStringContainsString('AnimalListSortingShape', $filenames['sorting']);
        self::assertStringContainsString('AnimalListFilteringShape', $filenames['filtering']);
    }
}
