<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\StringProperty;

class RouteRegexFactory
{
    public static function build(Route $route, ?ApivalkRequestDocumentation $documentation = null): string
    {
        $escaped = str_replace(['/', '.'], ['\/', '\.'], $route->getUrl());

        $regexPattern = preg_replace_callback(
            '#\{([a-zA-Z0-9_]+)\}#',
            // Use custom pattern from request documentation if the path property
            // defines one via setPattern(), otherwise fall back to the default.
            static function (array $matches) use ($documentation): string {
                $paramName = $matches[1];

                if ($documentation !== null) {
                    $pathProperties = $documentation->getPathProperties();
                    if (isset($pathProperties[$paramName])
                        && $pathProperties[$paramName] instanceof StringProperty
                        && $pathProperties[$paramName]->getPattern() !== null
                    ) {
                        return \sprintf('(%s)', $pathProperties[$paramName]->getPattern());
                    }
                }

                return '([a-zA-Z0-9_-]+)';
            },
            $escaped
        );

        return \sprintf('#^%s$#', $regexPattern);
    }
}
