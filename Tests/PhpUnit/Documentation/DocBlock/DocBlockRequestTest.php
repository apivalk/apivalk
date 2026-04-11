<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\DocBlock\DocBlockRequest;
use apivalk\apivalk\Documentation\DocBlock\DocBlockShape;

class DocBlockRequestTest extends TestCase
{
    public function testGetters(): void
    {
        $bodyShape = new DocBlockShape('User', 'Body');
        $pathShape = new DocBlockShape('User', 'Path');
        $queryShape = new DocBlockShape('User', 'Query');
        $orderingShape = new DocBlockShape('User', 'Ordering');

        $request = new DocBlockRequest($bodyShape, $pathShape, $queryShape, $orderingShape, null);

        $this->assertSame($bodyShape, $request->getBodyShape());
        $this->assertSame($pathShape, $request->getPathShape());
        $this->assertSame($queryShape, $request->getQueryShape());
        $this->assertSame($orderingShape, $request->getOrderingShape());
    }

    public function testGetRequestDocBlockOnly(): void
    {
        $bodyShape = new DocBlockShape('User', 'Body');
        $pathShape = new DocBlockShape('User', 'Path');
        $queryShape = new DocBlockShape('User', 'Query');
        $orderingShape = new DocBlockShape('User', 'Ordering');

        $request = new DocBlockRequest($bodyShape, $pathShape, $queryShape, $orderingShape, null);

        $docBlock = $request->getRequestDocBlockOnly('App\\Api\\Shape');

        $this->assertStringContainsString(
            '@method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\\App\\Api\\Shape\\UserQueryShape query()',
            $docBlock
        );
        $this->assertStringContainsString(
            '@method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\\App\\Api\\Shape\\UserPathShape path()',
            $docBlock
        );
        $this->assertStringContainsString(
            '@method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\\App\\Api\\Shape\\UserBodyShape body()',
            $docBlock
        );
    }

    public function testGetShapeNamespace(): void
    {
        $bodyShape = new DocBlockShape('User', 'Body');
        $pathShape = new DocBlockShape('User', 'Path');
        $queryShape = new DocBlockShape('User', 'Query');
        $orderingShape = new DocBlockShape('User', 'Ordering');

        $request = new DocBlockRequest($bodyShape, $pathShape, $queryShape, $orderingShape, null);

        $this->assertEquals('App\\Api\\Shape', $request->getShapeNamespace('App\\Api'));
    }

    public function testGetShapeFilenames(): void
    {
        $bodyShape = new DocBlockShape('User', 'Body');
        $pathShape = new DocBlockShape('User', 'Path');
        $queryShape = new DocBlockShape('User', 'Query');
        $orderingShape = new DocBlockShape('User', 'Ordering');

        $request = new DocBlockRequest($bodyShape, $pathShape, $queryShape, $orderingShape, null);

        $filenames = $request->getShapeFilenames('src/Api');

        $this->assertEquals('src/Api/Shape/UserPathShape.php', $filenames['path']);
        $this->assertEquals('src/Api/Shape/UserQueryShape.php', $filenames['query']);
        $this->assertEquals('src/Api/Shape/UserBodyShape.php', $filenames['body']);
        $this->assertEquals('src/Api/Shape/UserOrderingShape.php', $filenames['ordering']);
    }
}
