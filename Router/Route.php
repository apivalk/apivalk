<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Security\RouteAuthorization;

class Route
{
    /** @var string */
    private $url;
    /** @var MethodInterface */
    private $method;
    /** @var string|null */
    private $description;
    /** @var RouteAuthorization */
    private $routeAuthorization;
    /** @var TagObject[] */
    private $tags;
    /** @var null|RateLimitInterface */
    private $rateLimit;

    /**
     * @param string                  $url
     * @param MethodInterface         $method
     * @param string|null             $description
     * @param TagObject[]             $tags
     * @param RouteAuthorization|null $routeAuthorization
     * @param RateLimitInterface|null $rateLimit
     */
    public function __construct(
        string $url,
        MethodInterface $method,
        ?string $description = null,
        ?array $tags = null,
        ?RouteAuthorization $routeAuthorization = null,
        ?RateLimitInterface $rateLimit = null
    ) {
        $this->url = $url;
        $this->method = $method;
        $this->description = $description;
        $this->tags = $tags ?? [];
        $this->routeAuthorization = $routeAuthorization;
        $this->rateLimit = $rateLimit;
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

    public function getRouteAuthorization(): ?RouteAuthorization
    {
        return $this->routeAuthorization;
    }

    /** @return TagObject[] */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getRateLimit(): ?RateLimitInterface
    {
        return $this->rateLimit;
    }
}
