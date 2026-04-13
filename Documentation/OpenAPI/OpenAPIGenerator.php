<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI;

use apivalk\apivalk\Apivalk;
use apivalk\apivalk\Documentation\OpenAPI\Generator\PathsGenerator;
use apivalk\apivalk\Documentation\OpenAPI\Object\ComponentsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ServerObject;

class OpenAPIGenerator
{
    /** @var Apivalk */
    private $apivalk;
    /** @var OpenAPI */
    private $openApi;
    /** @var bool */
    private $documentLocaleHeaders;

    public const FORMAT_JSON = 'json';

    /**
     * @param Apivalk               $apivalk
     * @param InfoObject|null       $infoObject
     * @param ServerObject[]        $servers
     * @param ComponentsObject|null $componentsObject
     * @param bool                  $documentLocaleHeaders
     */
    public function __construct(
        Apivalk $apivalk,
        ?InfoObject $infoObject = null,
        array $servers = [],
        ?ComponentsObject $componentsObject = null,
        bool $documentLocaleHeaders = true
    ) {
        $this->apivalk = $apivalk;
        $this->openApi = new OpenAPI();
        $this->documentLocaleHeaders = $documentLocaleHeaders;

        if ($infoObject !== null) {
            $this->openApi->setInfo($infoObject);
        }

        if ($componentsObject !== null) {
            $this->openApi->setComponents($componentsObject);
        }

        foreach ($servers as $server) {
            $this->openApi->addServer($server);
        }
    }

    public function generate(string $format = 'json'): string
    {
        $this->generatePaths();

        if ($format === self::FORMAT_JSON) {
            return $this->openApi->toJson();
        }

        throw new \InvalidArgumentException(\sprintf('Format "%s" not supported', $format));
    }

    private function generatePaths(): void
    {
        $pathsGenerator = new PathsGenerator($this->documentLocaleHeaders);
        $routeMapping = [];

        foreach ($this->apivalk->getRouter()->getRoutes() as $route) {
            $routeMapping[$route['route']->getUrl()][] =
                ['route' => $route['route'], 'controllerClass' => $route['controllerClass']];
        }

        foreach ($routeMapping as $url => $routes) {
            $this->openApi->addPaths($pathsGenerator->generate($url, $routes));
        }
    }
}
