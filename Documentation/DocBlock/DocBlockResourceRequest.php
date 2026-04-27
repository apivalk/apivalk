<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Sort\SortBag;

class DocBlockResourceRequest
{
    /** @var DocBlockShape */
    private $sortingShape;
    /** @var DocBlockShape */
    private $filteringShape;
    /** @var string|null */
    private $paginatorClass;
    /** @var string */
    private $baseRequestClass;

    public function __construct(
        DocBlockShape $sortingShape,
        DocBlockShape $filteringShape,
        ?string $paginatorClass,
        string $baseRequestClass
    ) {
        $this->sortingShape = $sortingShape;
        $this->filteringShape = $filteringShape;
        $this->paginatorClass = $paginatorClass;
        $this->baseRequestClass = $baseRequestClass;
    }

    public function getSortingShape(): DocBlockShape
    {
        return $this->sortingShape;
    }

    public function getFilteringShape(): DocBlockShape
    {
        return $this->filteringShape;
    }

    public function getBaseRequestClass(): string
    {
        return $this->baseRequestClass;
    }

    public function getShapeNamespace(string $requestNamespace): string
    {
        return $requestNamespace . '\\Shape';
    }

    /** @return array<string, string> */
    public function getShapeFilenames(string $requestFolder): array
    {
        return [
            'sorting'   => \sprintf('%s/Shape/%s.php', $requestFolder, $this->sortingShape->getClassName()),
            'filtering' => \sprintf('%s/Shape/%s.php', $requestFolder, $this->filteringShape->getClassName()),
        ];
    }

    public function getDocBlockOnly(string $shapeNamespace): string
    {
        $lines = [
            ' * @method \\' . SortBag::class . '|\\' . $shapeNamespace . '\\' . $this->sortingShape->getClassName() . ' sorting()',
            ' * @method \\' . FilterBag::class . '|\\' . $shapeNamespace . '\\' . $this->filteringShape->getClassName() . ' filtering()',
        ];

        if ($this->paginatorClass !== null) {
            $lines[] = ' * @method \\' . $this->paginatorClass . ' paginator()';
        }

        return "/**\n" . implode("\n", $lines) . "\n */";
    }
}
