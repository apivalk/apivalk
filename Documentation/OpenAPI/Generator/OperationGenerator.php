<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Object\OperationObject;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Http\Response\MethodNotAllowedApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\TooManyRequestsApivalkResponse;
use apivalk\apivalk\Http\Response\UnauthorizedApivalkResponse;
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

        $responses = [];

        /** @var AbstractApivalkResponse $responseClass */
        foreach ($responseClasses as $responseClass) {
            $responses[] =
                $responseGenerator->generate((int)$responseClass::getStatusCode(), $responseClass::getDocumentation());
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

    private function getOrderProperty(Route $route): ?StringProperty
    {
        if (\count($route->getOrderings()) === 0) {
            return null;
        }

        $fields = [];

        foreach ($route->getOrderings() as $ordering) {
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
        $orderProperty->setExample($example);

        return $orderProperty;
    }
}
