<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\OpenAPI\Object\PathsObject;

class PathsGenerator
{
    /**
     * @param string                                                      $url
     * @param array<string, array{controllerClass: string, route: string}> $routes All routes with controller class name that have the same URL pattern but different methods. Example: ['controllerClass' => 'CreateController', 'route' => $createControllerRoute]
     *
     * @return PathsObject
     */
    public function generate(string $url, array $routes): PathsObject
    {
        $pathItemGenerator = new PathItemGenerator();

        return new PathsObject($url, $pathItemGenerator->generate($routes));
    }
}
