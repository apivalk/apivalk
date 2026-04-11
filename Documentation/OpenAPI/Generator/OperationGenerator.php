<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Object\OperationObject;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Http\Response\MethodNotAllowedApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\TooManyRequestsApivalkResponse;
use apivalk\apivalk\Http\Response\UnauthorizedApivalkResponse;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;

class OperationGenerator
{
    public function generate(
        Route $route,
        ApivalkRequestDocumentation $requestDocumentation,
        array $responseClasses
    ): OperationObject {
        $parameterGenerator = new ParameterGenerator();
        $requestBodyGenerator = new RequestBodyGenerator();
        $responseGenerator = new ResponseGenerator();

        $parameters = [];
        foreach ($requestDocumentation->getPathProperties() as $pathProperty) {
            $parameters[] = $parameterGenerator->generate($pathProperty, 'path');
        }

        foreach ($requestDocumentation->getQueryProperties() as $pathProperty) {
            $parameters[] = $parameterGenerator->generate($pathProperty, 'query');
        }

        $orderProperty = $this->getOrderProperty($route);
        if ($orderProperty !== null) {
            $parameters[] = $parameterGenerator->generate($orderProperty, 'query');
        }

        foreach ($this->getPaginationProperties($route) as $paginationProperty) {
            $parameters[] = $parameterGenerator->generate($paginationProperty, 'query');
        }

        foreach ($this->getFilterProperties($route) as $filterProperty) {
            $filterProperty->setIsRequired(false);

            $parameters[] = $parameterGenerator->generate($filterProperty, 'query');
        }

        $responses = [];

        /** @var AbstractApivalkResponse $responseClass */
        foreach ($responseClasses as $responseClass) {
            $responses[] =
                $responseGenerator->generate(
                    (int)$responseClass::getStatusCode(),
                    $responseClass::getDocumentation(),
                    $route
                );
        }

        // Todo: Maybe define the default responses in all operations in apivalk configuration
        $responses[] = $responseGenerator->generate(
            BadValidationApivalkResponse::getStatusCode(),
            BadValidationApivalkResponse::getDocumentation()
        );

        $responses[] = $responseGenerator->generate(
            MethodNotAllowedApivalkResponse::getStatusCode(),
            MethodNotAllowedApivalkResponse::getDocumentation()
        );

        $responses[] = $responseGenerator->generate(
            NotFoundApivalkResponse::getStatusCode(),
            NotFoundApivalkResponse::getDocumentation()
        );

        $responses[] = $responseGenerator->generate(
            TooManyRequestsApivalkResponse::getStatusCode(),
            TooManyRequestsApivalkResponse::getDocumentation()
        );

        $responses[] = $responseGenerator->generate(
            UnauthorizedApivalkResponse::getStatusCode(),
            UnauthorizedApivalkResponse::getDocumentation()
        );

        return new OperationObject(
            $route->getMethod(),
            $route->getTags(),
            $route->getSummary(),
            $route->getDescription(),
            \sprintf('%s_%s', $route->getUrl(), $route->getMethod()->getName()),
            $parameters,
            $requestBodyGenerator->generate($requestDocumentation, $route),
            $responses,
            $route->getRouteAuthorization()
        );
    }

    /**
     * @param Route $route
     *
     * @return AbstractProperty[]
     */
    private function getPaginationProperties(Route $route): array
    {
        if ($route->getPagination() === null) {
            return [];
        }

        $properties = [];
        $paginationType = $route->getPagination()->getType();

        $limitProperty = new NumberProperty(
            'limit',
            \sprintf(
                'Maximum number of items per page. Maximum value is %s.',
                $route->getPagination()->getMaxLimit()
            ),
            NumberProperty::FORMAT_INT64
        );
        $limitProperty->setExample('10');
        $limitProperty->setMinimumValue(1);
        $limitProperty->setMaximumValue($route->getPagination()->getMaxLimit());
        $limitProperty->setIsRequired(false);

        $properties[] = $limitProperty;

        switch ($paginationType) {
            case Pagination::TYPE_OFFSET:
                $offsetProperty = new NumberProperty(
                    'offset',
                    'Number of items to skip before starting to collect the result set (>= 0).',
                    NumberProperty::FORMAT_INT64
                );

                $offsetProperty->setExample('0');
                $offsetProperty->setMinimumValue(0);
                $offsetProperty->setIsRequired(false);

                $properties[] = $offsetProperty;

                break;

            case Pagination::TYPE_CURSOR:
                $cursorProperty = new StringProperty(
                    'cursor',
                    'Opaque cursor returned by the API. Use it to fetch the next page. Do not construct manually.'
                );

                $cursorProperty->setExample('eyJpZCI6MTIzfQ==');
                $cursorProperty->setIsRequired(false);

                $properties[] = $cursorProperty;

                break;

            case Pagination::TYPE_PAGE:
                $pageProperty = new NumberProperty(
                    'page',
                    'Page number to retrieve (starting from 1).',
                    NumberProperty::FORMAT_INT64
                );

                $pageProperty->setExample('1');
                $pageProperty->setMinimumValue(1);
                $pageProperty->setIsRequired(false);

                $properties[] = $pageProperty;

                break;
        }

        return $properties;
    }

    private function getOrderProperty(Route $route): ?StringProperty
    {
        if (\count($route->getSortings()) === 0) {
            return null;
        }

        $fields = [];

        foreach ($route->getSortings() as $ordering) {
            $fields[] = preg_quote($ordering->getField(), '/');
        }

        $group = implode('|', $fields);

        $regex = \sprintf(
            '^([+-](%s))(,([+-](%s)))*$',
            $group,
            $group
        );

        $orderProperty = new StringProperty(
            'order_by',
            'Comma-separated list of fields prefixed with + (asc) or - (desc)'
        );

        $exampleParts = [];
        foreach ($fields as $index => $field) {
            $prefix = $index === 0 ? '+' : '-';
            $exampleParts[] = $prefix . $field;
        }

        $example = implode(',', $exampleParts);

        $orderProperty->setPattern($regex);
        $orderProperty->setIsRequired(false);

        return $orderProperty;
    }

    /**
     * @param Route $route
     *
     * @return AbstractProperty[]
     */
    private function getFilterProperties(Route $route): array
    {
        if (\count($route->getFilters()) === 0) {
            return [];
        }
    
        $properties = [];
        foreach ($route->getFilters() as $filter) {
            $properties[] = $filter->getProperty();
        }
    
        return $properties;
    }
}
