<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI;

use apivalk\apivalk\Apivalk;
use apivalk\apivalk\Documentation\OpenAPI\Generator\PathsGenerator;
use apivalk\apivalk\Documentation\OpenAPI\Object\ComponentsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\OAuthFlowObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\OAuthFlowsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecuritySchemeObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ServerObject;

class OpenAPIGenerator
{
    /** @var Apivalk */
    private $apivalk;
    /** @var OpenAPI */
    private $openApi;

    public const FORMAT_JSON = 'json';

    /**
     * @param Apivalk               $apivalk
     * @param InfoObject|null       $infoObject
     * @param ServerObject[]        $servers
     * @param ComponentsObject|null $componentsObject
     */
    public function __construct(
        Apivalk $apivalk,
        ?InfoObject $infoObject = null,
        array $servers = [],
        ?ComponentsObject $componentsObject = null
    ) {
        $this->apivalk = $apivalk;
        $this->openApi = new OpenAPI();

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
        $this->collectScopes();

        if ($format === self::FORMAT_JSON) {
            return $this->openApi->toJson();
        }

        throw new \InvalidArgumentException(\sprintf('Format "%s" not supported', $format));
    }

    private function generatePaths(): void
    {
        $pathsGenerator = new PathsGenerator();
        $routeMapping = [];

        foreach ($this->apivalk->getRouter()->getRoutes() as $route) {
            $routeMapping[$route['route']->getUrl()][] =
                ['route' => $route['route'], 'controllerClass' => $route['controllerClass']];
        }

        foreach ($routeMapping as $url => $routes) {
            $this->openApi->addPaths($pathsGenerator->generate($url, $routes));
        }
    }

    private function collectScopes(): void
    {
        $components = $this->openApi->getComponents();
        $securitySchemes = $components->getSecuritySchemes();

        foreach ($this->apivalk->getRouter()->getRoutes() as $routeContainer) {
            $route = $routeContainer['route'];
            foreach ($route->getSecurityRequirements() as $requirement) {
                $schemeName = $requirement->getName();
                if ($schemeName === null) {
                    continue;
                }

                if (!isset($securitySchemes[$schemeName])) {
                    // Automatically create a default security scheme if it's missing
                    $type = 'apiKey';
                    $in = 'header';
                    $scheme = null;
                    $flows = null;

                    if (stripos($schemeName, 'bearer') !== false) {
                        $type = 'http';
                        $scheme = 'bearer';
                        $in = null;
                    } elseif (stripos($schemeName, 'oauth2') !== false) {
                        $type = 'oauth2';
                        $in = null;
                        $flows = new OAuthFlowsObject(
                            null,
                            new OAuthFlowObject('', ''),
                            null,
                            null
                        );
                    } elseif (stripos($schemeName, 'fido') !== false) {
                        $type = 'apiKey';
                        $in = 'header';
                    }

                    $securitySchemes[$schemeName] = new SecuritySchemeObject(
                        $type,
                        $schemeName,
                        'Auto-generated security scheme',
                        $in,
                        $scheme,
                        null,
                        $flows,
                        null
                    );
                    $components->setSecuritySchemes($securitySchemes);
                }
                $schemeObj = $securitySchemes[$schemeName];

                $flows = $schemeObj->getFlows();
                if ($flows === null) {
                    continue;
                }

                $allFlows = array_filter(
                    [
                        $flows->getImplicit(),
                        $flows->getPassword(),
                        $flows->getClientCredentials(),
                        $flows->getAuthorizationCode()
                    ]
                );

                foreach ($requirement->getScopes() as $scope) {
                    foreach ($allFlows as $flow) {
                        $flow->addScope($scope->getName(), $scope->getDescription() ?? '');
                    }
                }
            }
        }
    }
}
