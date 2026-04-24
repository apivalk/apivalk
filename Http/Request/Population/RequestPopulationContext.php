<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Population;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Router\Route\Route;

class RequestPopulationContext
{
    /** @var Route */
    private $route;
    /** @var ApivalkRequestDocumentation */
    private $documentation;

    public function __construct(Route $route, ApivalkRequestDocumentation $documentation)
    {
        $this->route = $route;
        $this->documentation = $documentation;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    public function getDocumentation(): ApivalkRequestDocumentation
    {
        return $this->documentation;
    }
}
