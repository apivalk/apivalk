<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

class DocBlockRequest
{
    /** @var DocBlockShape */
    private $bodyShape;
    /** @var DocBlockShape */
    private $pathShape;
    /** @var DocBlockShape */
    private $queryShape;
    /** @var DocBlockShape */
    private $sortingShape;
    /** @var DocBlockShape */
    private $filteringShape;
    /** @var string|null */
    private $paginatorClass;

    public function __construct(
        DocBlockShape $bodyShape,
        DocBlockShape $pathShape,
        DocBlockShape $queryShape,
        DocBlockShape $sortingShape,
        DocBlockShape $filteringShape,
        ?string $paginatorClass
    ) {
        $this->bodyShape = $bodyShape;
        $this->pathShape = $pathShape;
        $this->queryShape = $queryShape;
        $this->sortingShape = $sortingShape;
        $this->filteringShape = $filteringShape;
        $this->paginatorClass = $paginatorClass;
    }

    public function getBodyShape(): DocBlockShape
    {
        return $this->bodyShape;
    }

    public function getPathShape(): DocBlockShape
    {
        return $this->pathShape;
    }

    public function getQueryShape(): DocBlockShape
    {
        return $this->queryShape;
    }

    public function getSortingShape(): DocBlockShape
    {
        return $this->sortingShape;
    }

    public function getFilteringShape(): DocBlockShape
    {
        return $this->filteringShape;
    }

    public function getRequestDocBlockOnly(string $shapeNamespace): string
    {
        $string = <<<'PHP'
/**
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\{{QUERY_SHAPE_CLASS}} query()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\{{PATH_SHAPE_CLASS}} path()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\{{BODY_SHAPE_CLASS}} body()
 * @method \apivalk\apivalk\Router\Route\Sort\SortBag|\{{SORTING_SHAPE_CLASS}} sorting()
 * @method \apivalk\apivalk\Router\Route\Filter\FilterBag|\{{FILTERING_SHAPE_CLASS}} filtering()
 * @method \{{PAGINATOR_CLASS}} paginator()
 */
PHP;

        return str_replace(
            [
                '{{QUERY_SHAPE_CLASS}}',
                '{{PATH_SHAPE_CLASS}}',
                '{{BODY_SHAPE_CLASS}}',
                '{{SORTING_SHAPE_CLASS}}',
                '{{FILTERING_SHAPE_CLASS}}',
                '{{PAGINATOR_CLASS}}'
            ],
            [
                $shapeNamespace . '\\' . $this->queryShape->getClassName(),
                $shapeNamespace . '\\' . $this->pathShape->getClassName(),
                $shapeNamespace . '\\' . $this->bodyShape->getClassName(),
                $shapeNamespace . '\\' . $this->sortingShape->getClassName(),
                $shapeNamespace . '\\' . $this->filteringShape->getClassName(),
                $this->paginatorClass !== null ? $this->paginatorClass . '|null' : 'null',
            ],
            $string
        );
    }

    public function getShapeNamespace(string $requestNamespace): string
    {
        return \sprintf('%s\\Shape', $requestNamespace);
    }

    public function getShapeFilenames(string $requestFolder): array
    {
        return [
            'path' => \sprintf('%s/Shape/%s.php', $requestFolder, $this->pathShape->getClassName()),
            'query' => \sprintf('%s/Shape/%s.php', $requestFolder, $this->queryShape->getClassName()),
            'body' => \sprintf('%s/Shape/%s.php', $requestFolder, $this->bodyShape->getClassName()),
            'sorting' => \sprintf('%s/Shape/%s.php', $requestFolder, $this->sortingShape->getClassName()),
            'filtering' => \sprintf('%s/Shape/%s.php', $requestFolder, $this->filteringShape->getClassName()),
        ];
    }
}
