<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Cache;

use apivalk\apivalk\Router\Route;

class RouterCacheEntry implements \JsonSerializable
{
    /** @var Route */
    private $route;
    /** @var string */
    private $regex;
    /** @var string */
    private $controllerClass;

    public function __construct(Route $route, string $regex, string $controllerClass)
    {
        $this->route = $route;
        $this->regex = $regex;
        $this->controllerClass = $controllerClass;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    public function jsonSerialize(): array
    {
        return [
            'route' => $this->route,
            'regex' => $this->regex,
            'controllerClass' => $this->controllerClass,
        ];
    }
}
