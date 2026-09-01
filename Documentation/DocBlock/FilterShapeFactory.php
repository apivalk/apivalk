<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\Operator;

/**
 * Builds one shape per filter field. The operator accessors exist on the filter class
 * regardless, but the class carries every operator its type supports, so the generated
 * interface is what narrows the IDE down to the operators the field actually declares.
 */
final class FilterShapeFactory
{
    public static function build(string $requestName, FilterInterface $filter): DocBlockShape
    {
        $shape = new DocBlockShape($requestName, self::shapeType($filter->getField()));
        $phpType = $filter->getProperty()->getPhpType();

        if (strpos($phpType, '\\') !== false && $phpType[0] !== '\\') {
            $phpType = '\\' . $phpType;
        }

        $description = $filter->getProperty()->getPropertyDescription();

        foreach ($filter->getAllowedOperators() as $operator) {
            $shape->addCustomField(
                Operator::accessorFor($operator),
                self::returnType($operator, $phpType),
                trim($description . ', ' . self::operatorDescription($operator), ', ')
            );
        }

        $shape->addMethod('has(string $operator)', 'bool');
        $shape->addMethod('raw(string $operator)', 'string|null');

        return $shape;
    }

    /**
     * Lets the filtering shape stand in for FilterBag on its own. Naming both in a union
     * would resolve `$filters->field` through `FilterBag::__get()` as well, and report
     * `FilterInterface|null` next to the field shape.
     */
    public static function decorateBagShape(DocBlockShape $shape): void
    {
        $shape->extendsInterface('\\IteratorAggregate');
        $shape->addMethod('has(string $field)', 'bool');
        $shape->addMethod('get(string $field)', '\\' . FilterInterface::class . '|null');
        $shape->addMethod('all()', '\\' . FilterInterface::class . '[]');
        $shape->addMethod('count()', 'int');
    }

    public static function shapeType(string $field): string
    {
        return 'Filter' . str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $field)));
    }

    private static function operatorDescription(string $operator): string
    {
        switch ($operator) {
            case Operator::EQ:
                return 'exact match';
            case Operator::NEQ:
                return 'excludes this value';
            case Operator::IN:
                return 'matches any of the listed values';
            case Operator::GT:
                return 'strictly greater';
            case Operator::GTE:
                return 'greater or equal';
            case Operator::LT:
                return 'strictly lower';
            case Operator::LTE:
                return 'lower or equal';
            case Operator::LIKE:
                return 'pattern match';
            case Operator::CONTAINS:
                return 'substring match';
            case Operator::NULL:
                return 'true matches null, false matches non-null';
            default:
                return '';
        }
    }

    private static function returnType(string $operator, string $phpType): string
    {
        if ($operator === Operator::NULL) {
            return 'bool|null';
        }

        if ($operator === Operator::IN) {
            return $phpType . '[]';
        }

        return $phpType . '|null';
    }
}
