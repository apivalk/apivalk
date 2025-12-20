<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Http\Method\MethodFactory;
use apivalk\apivalk\Http\Method\MethodInterface;

class Route implements \JsonSerializable
{
    /** @var string */
    private $url;
    /** @var MethodInterface */
    private $method;
    /** @var string|null */
    private $description;
    /** @var SecurityRequirementObject[] */
    private $securityRequirements;
    /** @var TagObject[] */
    private $tags;

    /**
     * @param string                      $url
     * @param MethodInterface             $method
     * @param string|null                 $description
     * @param TagObject[]                 $tags
     * @param SecurityRequirementObject[] $securityRequirements
     */
    public function __construct(
        string $url,
        MethodInterface $method,
        ?string $description = null,
        array $tags = [],
        array $securityRequirements = []
    ) {
        $this->url = $url;
        $this->method = $method;
        $this->description = $description;
        $this->tags = $tags;
        $this->securityRequirements = $securityRequirements;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMethod(): MethodInterface
    {
        return $this->method;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getSecurityRequirements(): array
    {
        return $this->securityRequirements;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function jsonSerialize(): array
    {
        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = ['name' => $tag->getName(), 'description' => $tag->getDescription()];
        }

        $securityRequirements = [];
        foreach ($this->securityRequirements as $securityRequirement) {
            $securityRequirements[] =
                ['name' => $securityRequirement->getName(), 'scopes' => $securityRequirement->getScopes()];
        }

        return [
            'url' => $this->url,
            'method' => $this->method->getName(),
            'description' => $this->description,
            'tags' => $tags,
            'securityRequirements' => $securityRequirements,
        ];
    }

    /** @param string $json should contain JSON in the format returned by Route::jsonSerialize */
    public static function byJson(string $json): self
    {
        $jsonArray = json_decode($json, true);

        if (!\is_array($jsonArray)) {
            throw new \InvalidArgumentException('Invalid JSON provided to Route::byJson');
        }

        if (!isset($jsonArray['url'], $jsonArray['method'])) {
            throw new \InvalidArgumentException('Missing required keys (url, method) in Route JSON');
        }

        $tags = [];
        foreach ($jsonArray['tags'] ?? [] as $tag) {
            $tags[] = new TagObject($tag['name'], $tag['description']);
        }

        $securityRequirements = [];
        foreach ($jsonArray['securityRequirements'] ?? [] as $securityRequirement) {
            $securityRequirements[] =
                new SecurityRequirementObject($securityRequirement['name'], $securityRequirement['scopes']);
        }

        return new self(
            $jsonArray['url'],
            MethodFactory::create($jsonArray['method']),
            $jsonArray['description'] ?? null,
            $tags,
            $securityRequirements
        );
    }
}
