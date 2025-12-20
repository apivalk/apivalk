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

    public function __construct(
        DocBlockShape $bodyShape,
        DocBlockShape $pathShape,
        DocBlockShape $queryShape
    ) {
        $this->bodyShape = $bodyShape;
        $this->pathShape = $pathShape;
        $this->queryShape = $queryShape;
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

    public function getRequestDocBlockOnly(string $shapeNamespace): string
    {
        $string = <<<'PHP'
/**
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\{{QUERY_SHAPE_CLASS}} query()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\{{PATH_SHAPE_CLASS}} path()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\{{BODY_SHAPE_CLASS}} body()
 */
PHP;

        return str_replace(
            ['{{QUERY_SHAPE_CLASS}}', '{{PATH_SHAPE_CLASS}}', '{{BODY_SHAPE_CLASS}}'],
            [
                $shapeNamespace . '\\' . $this->queryShape->getClassName(),
                $shapeNamespace . '\\' . $this->pathShape->getClassName(),
                $shapeNamespace . '\\' . $this->bodyShape->getClassName(),
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
            'path' => \sprintf('%s/%s.php', $requestFolder, $this->pathShape->getClassName()),
            'query' => \sprintf('%s/%s.php', $requestFolder, $this->queryShape->getClassName()),
            'body' => \sprintf('%s/%s.php', $requestFolder, $this->bodyShape->getClassName()),
        ];
    }
}
