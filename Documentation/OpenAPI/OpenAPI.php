<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI;

use apivalk\apivalk\Documentation\OpenAPI\Object\ComponentsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathItemObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ServerObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;

/**
 * Class OpenAPI
 *
 * @see     https://swagger.io/specification/#openapi-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI
 */
class OpenAPI
{
    /** @var string */
    private $openapi = '3.1.1';
    /** @var InfoObject|null */
    private $info;
    /** @var string|null */
    private $jsonSchemaDialect;
    /** @var ServerObject[] */
    private $servers = [];
    /** @var PathsObject[] */
    private $paths = [];
    /** @var array<string, PathItemObject> */
    private $webhooks = [];
    /** @var ComponentsObject */
    private $components;
    /** @var TagObject[] */
    private $tags = [];

    public function setInfo(InfoObject $info): void
    {
        $this->info = $info;
    }

    public function setJsonSchemaDialect(string $jsonSchemaDialect): void
    {
        $this->jsonSchemaDialect = $jsonSchemaDialect;
    }

    public function addServer(ServerObject $server): void
    {
        $this->servers[] = $server;
    }

    public function addPaths(PathsObject $paths): void
    {
        $this->paths[] = $paths;
    }

    public function addWebhook(string $name, PathItemObject $pathItem): void
    {
        $this->webhooks[$name] = $pathItem;
    }

    public function setComponents(ComponentsObject $components): void
    {
        $this->components = $components;
    }

    public function addTag(TagObject $tag): void
    {
        $this->tags[] = $tag;
    }

    public function getOpenapi(): string
    {
        return $this->openapi;
    }

    public function getInfo(): ?InfoObject
    {
        return $this->info;
    }

    public function getJsonSchemaDialect(): ?string
    {
        return $this->jsonSchemaDialect;
    }

    public function getServers(): array
    {
        return $this->servers;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    public function getComponents(): ComponentsObject
    {
        return $this->components;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function toArray(): array
    {
        $servers = [];
        foreach ($this->servers as $server) {
            $servers[] = $server->toArray();
        }

        $paths = [];
        foreach ($this->paths as $path) {
            $paths += $path->toArray();
        }

        $webhooks = array_map(static function ($path) {
            return $path->toArray();
        }, $this->webhooks);

        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = $tag->toArray();
        }

        return array_filter([
            'openapi' => $this->openapi,
            'info' => $this->info instanceof InfoObject ? array_filter($this->info->toArray()) : [],
            'jsonSchemaDialect' => $this->jsonSchemaDialect,
            'servers' => $servers,
            'paths' => $paths,
            'webhooks' => $webhooks,
            'components' => $this->components instanceof ComponentsObject ? array_filter($this->components->toArray())
                : null,
            'tags' => $tags,
        ]);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
