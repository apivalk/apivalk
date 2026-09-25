<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI;

use apivalk\apivalk\Apivalk;
use apivalk\apivalk\Documentation\OpenAPI\Generator\PathsGenerator;
use apivalk\apivalk\Documentation\OpenAPI\Object\ComponentsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ServerObject;
use apivalk\apivalk\Router\Route\Route;

class OpenAPIGenerator
{
    private Apivalk $apivalk;
    private OpenAPI $openApi;
    private bool $documentLocaleHeaders;
    private bool $flatFilters;
    private bool $forceIncludeExcludedRoutes = false;
    /** @var string[] */
    private array $onlyWithTags = [];

    public const FORMAT_JSON = 'json';

    /**
     * @param Apivalk               $apivalk
     * @param InfoObject|null       $infoObject
     * @param ServerObject[]        $servers
     * @param ComponentsObject|null $componentsObject
     * @param bool                  $documentLocaleHeaders
     * @param bool                  $flatFilters           When false (default), the form follows the operators a
     *                                                     field declares: one operator is a flat query parameter
     *                                                     (?status=active), several are a deepObject parameter
     *                                                     (?amount[gte]=10). When true, multi-operator fields are
     *                                                     flattened as well and resolve to the first declared
     *                                                     operator. Both formats work at runtime regardless.
     */
    public function __construct(
        Apivalk $apivalk,
        ?InfoObject $infoObject = null,
        array $servers = [],
        ?ComponentsObject $componentsObject = null,
        bool $documentLocaleHeaders = true,
        bool $flatFilters = false
    ) {
        $this->apivalk = $apivalk;
        $this->openApi = new OpenAPI();
        $this->openApi->setSecuritySchemes($apivalk->getSecuritySchemes());
        $this->documentLocaleHeaders = $documentLocaleHeaders;
        $this->flatFilters = $flatFilters;

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

    /**
     * Document routes that are marked with `Route::excludeFromDocumentation()` anyway.
     *
     * Meant for an internal spec generated next to the public one, not as a default.
     */
    public function forceIncludeExcludedRoutes(bool $force = true): self
    {
        $this->forceIncludeExcludedRoutes = $force;

        return $this;
    }

    /**
     * Restrict the document to routes carrying at least one of the given tag names.
     *
     * Tag names are matched exactly and case sensitively. An empty list (default) documents
     * every route, including untagged ones. A non-empty list drops untagged routes.
     *
     * @param string[] $tagNames
     */
    public function onlyWithTags(array $tagNames): self
    {
        foreach ($tagNames as $tagName) {
            if ($tagName === '') {
                throw new \InvalidArgumentException('onlyWithTags() does not accept an empty tag name.');
            }
        }

        $this->onlyWithTags = $tagNames;

        return $this;
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
        $this->openApi->resetPaths();

        $pathsGenerator = new PathsGenerator($this->documentLocaleHeaders, $this->flatFilters);
        $routeMapping = [];
        $availableTagNames = [];

        foreach ($this->apivalk->getRouter()->getRoutes() as $route) {
            if ($route['route']->isExcludedFromDocumentation() && !$this->forceIncludeExcludedRoutes) {
                continue;
            }

            foreach ($route['route']->getTags() as $tag) {
                $availableTagNames[$tag->getName()] = true;
            }

            if (!$this->matchesTagFilter($route['route'])) {
                continue;
            }

            $routeMapping[$route['route']->getUrl()][] =
                ['route' => $route['route'], 'controllerClass' => $route['controllerClass']];
        }

        $this->assertOnlyWithTagsAreAvailable(\array_keys($availableTagNames));

        foreach ($routeMapping as $url => $routes) {
            $this->openApi->addPaths($pathsGenerator->generate($url, $routes));
        }
    }

    private function matchesTagFilter(Route $route): bool
    {
        if ($this->onlyWithTags === []) {
            return true;
        }

        foreach ($route->getTags() as $tag) {
            if (\in_array($tag->getName(), $this->onlyWithTags, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A tag name no documented route carries is a typo, not an empty section. Left alone it
     * produces a document without `paths`, which no longer validates against the OpenAPI schema.
     *
     * @param string[] $availableTagNames
     */
    private function assertOnlyWithTagsAreAvailable(array $availableTagNames): void
    {
        $unknownTagNames = \array_diff($this->onlyWithTags, $availableTagNames);

        if ($unknownTagNames === []) {
            return;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unknown OpenAPI tag(s) "%s" passed to onlyWithTags(). Documented routes carry: %s.',
            \implode('", "', $unknownTagNames),
            $availableTagNames === [] ? 'no tags at all' : '"' . \implode('", "', $availableTagNames) . '"'
        ));
    }
}
