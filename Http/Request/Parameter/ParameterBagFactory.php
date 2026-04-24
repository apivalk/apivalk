<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Parameter;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Router\Route\Route;

final class ParameterBagFactory
{
    public static function createHeaderBag(): ParameterBag
    {
        $headerBag = new ParameterBag();

        foreach ($_SERVER as $key => $value) {
            if (strncmp($key, 'HTTP_', 5) !== 0) {
                continue;
            }

            $headerBag->set(new Parameter(strtoupper(substr($key, 5)), $value, $value));
        }

        return $headerBag;
    }

    /**
     * @param AbstractProperty[] $queryProperties
     */
    public static function createQueryBag(Route $route, array $queryProperties): ParameterBag
    {
        $queryBag = new ParameterBag();

        foreach ($_GET as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (isset($queryProperties[$key])) {
                $queryBag->set(
                    new Parameter(
                        $key,
                        self::typeCastValueByProperty($value, $queryProperties[$key]),
                        $value
                    )
                );

                continue;
            }

            foreach ($route->getFilters() as $filter) {
                if ($filter->getField() === $key) {
                    $queryBag->set(
                        new Parameter(
                            $key,
                            self::typeCastValueByProperty($value, $filter->getProperty()),
                            $value
                        )
                    );

                    continue 2;
                }
            }
        }

        return $queryBag;
    }

    /**
     * @param AbstractProperty[] $pathProperties
     */
    public static function createPathBag(Route $route, array $pathProperties): ParameterBag
    {
        $pathBag = new ParameterBag();

        preg_match_all('/\{([a-zA-Z0-9]+)\}/', $route->getUrl(), $keyMatches);
        $parameterNames = $keyMatches[1];

        $url = $route->getUrl();
        $escapedUrl = preg_replace_callback(
            '/(\{[a-zA-Z0-9]+\})|([^{]+)/',
            static function ($matches) {
                if (!empty($matches[1])) {
                    return '([a-zA-Z0-9-_]+)';
                }

                return preg_quote($matches[2], '#');
            },
            $url
        );
        $regexPattern = '#^' . $escapedUrl . '$#';

        $requestUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!$requestUriPath) {
            throw new \RuntimeException('Invalid request URI');
        }

        $result = [];
        if (preg_match($regexPattern, $requestUriPath, $parameterValues)) {
            array_shift($parameterValues);

            if (\count($parameterNames) === \count($parameterValues)) {
                $result = array_combine($parameterNames, $parameterValues);
            }
        }

        foreach ($result as $parameterName => $parameterValue) {
            if (!isset($pathProperties[$parameterName])) {
                continue;
            }

            if ($parameterValue === null) {
                continue;
            }

            $pathBag->set(
                new Parameter(
                    $parameterName,
                    self::typeCastValueByProperty($parameterValue, $pathProperties[$parameterName]),
                    $parameterValue
                )
            );
        }

        return $pathBag;
    }

    /**
     * @param AbstractProperty[] $bodyProperties
     */
    public static function createBodyBag(array $bodyProperties): ParameterBag
    {
        $inputValues = [];

        $bodyJsonData = json_decode(file_get_contents('php://input'), true);
        if (\is_array($bodyJsonData)) {
            foreach ($bodyJsonData as $key => $value) {
                $inputValues[$key] = $value;
            }
        }

        foreach ($_POST as $key => $value) {
            $inputValues[$key] = $value;
        }

        $inputBag = new ParameterBag();

        foreach ($bodyProperties as $inputProperty) {
            $value = $inputValues[$inputProperty->getPropertyName()] ?? null;

            if ($value === null) {
                continue;
            }

            $inputBag->set(
                new Parameter(
                    $inputProperty->getPropertyName(),
                    self::typeCastValueByProperty($value, $inputProperty),
                    $value
                )
            );
        }

        return $inputBag;
    }

    /**
     * ToDo: Refactor this, move this to a class. Something like PropertyTypeCaster or so
     *
     * @return bool|float|int|object|array|string|null
     */
    public static function typeCastValueByProperty($value, AbstractProperty $property)
    {
        if (($property instanceof ArrayProperty)
            && \is_string($value)) {
            $value = json_decode($value, true);
        }

        switch ($property->getPhpType()) {
            case 'string':
                return (string)$value;
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'bool':
                return (bool)$value;
            case 'object':
            case 'array':
                if (\is_array($value)) {
                    return $value;
                }

                if (\is_string($value)) {
                    return json_decode($value, true);
                }

                return null;
            case '\DateTime':
                try {
                    return new \DateTime($value);
                } catch (\Exception $e) {
                    return null;
                }
            default:
                return null;
        }
    }
}
