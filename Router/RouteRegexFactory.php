<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

class RouteRegexFactory
{
    public static function build(Route $route): string
    {
        $escaped = str_replace(['/', '.'], ['\/', '\.'], $route->getUrl());
        $regexPattern = preg_replace('#\{[a-zA-Z0-9_]+\}#', '([a-zA-Z0-9_-]+)', $escaped);

        return \sprintf('#^%s$#', $regexPattern);
    }
}
