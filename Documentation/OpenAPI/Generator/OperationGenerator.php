<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Object\HeaderObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\OperationObject;
use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
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
    /** @var bool */
    private $documentLocaleHeaders;

    public function __construct(bool $documentLocaleHeaders = true)
    {
        $this->documentLocaleHeaders = $documentLocaleHeaders;
    }

    public function generate(
        Route $route,
        ApivalkRequestDocumentation $requestDocumentation,
        array $responseClasses
    ): OperationObject {
        $responseHeaders = $this->getResponseHeaders($route);

        $responseDocumentations = [];

        /** @var AbstractApivalkResponse $responseClass */
        foreach ($responseClasses as $responseClass) {
            $responseDocumentations[] = [
                'statusCode' => (int)$responseClass::getStatusCode(),
                'documentation' => $responseClass::getDocumentation(),
            ];
        }

        return $this->generateOperation(
            $route,
            $requestDocumentation,
            $responseDocumentations,
            $responseHeaders
        );
    }

    /**
     * Generate an operation using pre-built response documentation.
     *
     * @param Route                                                                           $route
     * @param ApivalkRequestDocumentation                                                     $requestDocumentation
     * @param array<int, array{statusCode: int, documentation: ApivalkResponseDocumentation}> $responseDocumentations
     *
     * @return OperationObject
     */
    public function generateFromDocumentation(
        Route $route,
        ApivalkRequestDocumentation $requestDocumentation,
        array $responseDocumentations
    ): OperationObject {
        $responseHeaders = $this->getResponseHeaders($route);

        return $this->generateOperation(
            $route,
            $requestDocumentation,
            $responseDocumentations,
            $responseHeaders
        );
    }

    /**
     * @param Route                                                                           $route
     * @param ApivalkRequestDocumentation                                                     $requestDocumentation
     * @param array<int, array{statusCode: int, documentation: ApivalkResponseDocumentation}> $responseDocumentations
     * @param array<string, HeaderObject>                                                     $responseHeaders
     *
     * @return OperationObject
     */
    private function generateOperation(
        Route $route,
        ApivalkRequestDocumentation $requestDocumentation,
        array $responseDocumentations,
        array $responseHeaders
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

        $orderProperty = self::getOrderProperty($route);
        if ($orderProperty !== null) {
            $parameters[] = $parameterGenerator->generate($orderProperty, 'query');
        }

        foreach (self::getPaginationProperties($route) as $paginationProperty) {
            $parameters[] = $parameterGenerator->generate($paginationProperty, 'query');
        }

        foreach (self::getFilterProperties($route) as $filterProperty) {
            $parameters[] = $parameterGenerator->generate($filterProperty, 'query');
        }

        if ($this->documentLocaleHeaders) {
            $acceptLanguageProperty = new StringProperty(
                'Accept-Language',
                'BCP 47 language tag for content negotiation (e.g. en, de-DE).'
            );
            $acceptLanguageProperty->setIsRequired(false);
            $acceptLanguageProperty->setExample('en');
            $parameters[] = $parameterGenerator->generate($acceptLanguageProperty, 'header');
        }

        $responses = [];

        foreach ($responseDocumentations as $responseDoc) {
            $responses[] = $responseGenerator->generate(
                $responseDoc['statusCode'],
                $responseDoc['documentation'],
                $route,
                $responseHeaders
            );
        }

        $responses[] = $responseGenerator->generate(
            BadValidationApivalkResponse::getStatusCode(),
            BadValidationApivalkResponse::getDocumentation(),
            null,
            $responseHeaders
        );

        $responses[] = $responseGenerator->generate(
            MethodNotAllowedApivalkResponse::getStatusCode(),
            MethodNotAllowedApivalkResponse::getDocumentation(),
            null,
            $responseHeaders
        );

        $responses[] = $responseGenerator->generate(
            NotFoundApivalkResponse::getStatusCode(),
            NotFoundApivalkResponse::getDocumentation(),
            null,
            $responseHeaders
        );

        $responses[] = $responseGenerator->generate(
            TooManyRequestsApivalkResponse::getStatusCode(),
            TooManyRequestsApivalkResponse::getDocumentation(),
            null,
            $responseHeaders
        );

        $responses[] = $responseGenerator->generate(
            UnauthorizedApivalkResponse::getStatusCode(),
            UnauthorizedApivalkResponse::getDocumentation(),
            null,
            $responseHeaders
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
    public static function getPaginationProperties(Route $route): array
    {
        if ($route->getPagination() === null) {
            return [];
        }

        $properties = [];
        $paginationType = $route->getPagination()->getType();

        $limitProperty = new IntegerProperty(
            'limit',
            \sprintf(
                'Maximum number of items per page. Maximum value is %s.',
                $route->getPagination()->getMaxLimit()
            ),
            IntegerProperty::FORMAT_INT64
        );
        $limitProperty->setExample('10');
        $limitProperty->setMinimumValue(1);
        $limitProperty->setMaximumValue($route->getPagination()->getMaxLimit());
        $limitProperty->setIsRequired(false);

        $properties[] = $limitProperty;

        switch ($paginationType) {
            case Pagination::TYPE_OFFSET:
                $offsetProperty = new IntegerProperty(
                    'offset',
                    'Number of items to skip before starting to collect the result set (>= 0).',
                    IntegerProperty::FORMAT_INT64
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
                $pageProperty = new IntegerProperty(
                    'page',
                    'Page number to retrieve (starting from 1).',
                    IntegerProperty::FORMAT_INT64
                );

                $pageProperty->setExample('1');
                $pageProperty->setMinimumValue(1);
                $pageProperty->setIsRequired(false);

                $properties[] = $pageProperty;

                break;
        }

        return $properties;
    }

    public static function getOrderProperty(Route $route): ?StringProperty
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
            '/^([+-](%s))(,([+-](%s)))*$/',
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

    /**
     * @param Route $route
     *
     * @return AbstractProperty[]
     */
    public static function getFilterProperties(Route $route): array
    {
        if (\count($route->getFilters()) === 0) {
            return [];
        }

        $properties = [];

        foreach ($route->getFilters() as $filter) {
            $filterProperty = $filter->getProperty();
            $filterProperty->setIsRequired(false);

            $properties[] = $filterProperty;
        }

        return $properties;
    }

    /**
     * @return array<string, HeaderObject>
     */
    private function getResponseHeaders(Route $route): array
    {
        $headers = [];

        if ($this->documentLocaleHeaders) {
            $headers['Content-Language'] =
                new HeaderObject('The locale of the response content (BCP 47 language tag).');
        }

        if ($route->getRateLimit() !== null) {
            $headers['X-RateLimit-Limit'] = new HeaderObject(
                \sprintf(
                    'The maximum number of requests allowed within the time window (%d seconds).',
                    $route->getRateLimit()->getWindowInSeconds()
                )
            );
            $headers['X-RateLimit-Remaining'] = new HeaderObject(
                'The number of requests remaining in the current time window.'
            );
            $headers['X-RateLimit-Reset'] = new HeaderObject(
                'The UTC epoch timestamp (in seconds) when the rate limit window resets.'
            );
            $headers['Retry-After'] = new HeaderObject(
                'The UTC epoch timestamp (in seconds) after which the client may retry. Present only when the rate limit has been exceeded.'
            );
        }

        return $headers;
    }
}
