<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route;

use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Http\Method\DeleteMethod;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Method\PatchMethod;
use apivalk\apivalk\Http\Method\PostMethod;
use apivalk\apivalk\Http\Method\PutMethod;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Router\Route\Order\Order;
use apivalk\apivalk\Security\RouteAuthorization;

class Route
{
    /** @var string */
    private $url;
    /** @var MethodInterface */
    private $method;
    /** @var string|null */
    private $description;
    /** @var string|null */
    private $summary;
    /** @var RouteAuthorization */
    private $routeAuthorization;
    /** @var TagObject[] */
    private $tags;
    /** @var null|RateLimitInterface */
    private $rateLimit;
    /** @var Order[] */
    private $orderings;

    /**
     * @param string                  $url
     * @param MethodInterface         $method
     * @param string|null             $description
     * @param string|null             $summary
     * @param TagObject[]             $tags
     * @param RouteAuthorization|null $routeAuthorization
     * @param RateLimitInterface|null $rateLimit
     * @param Order[]|null            $orderings
     */
    public function __construct(
        string $url,
        MethodInterface $method,
        ?string $description = null,
        ?string $summary = null,
        ?array $tags = null,
        ?RouteAuthorization $routeAuthorization = null,
        ?RateLimitInterface $rateLimit = null,
        ?array $orderings = null
    ) {
        $this->url = $url;
        $this->method = $method;
        $this->description = $description;
        $this->summary = $summary;
        $this->tags = $tags ?? [];
        $this->routeAuthorization = $routeAuthorization;
        $this->rateLimit = $rateLimit;
        $this->orderings = $orderings ?? [];
    }

    public static function get(string $url): self
    {
        return new self($url, new GetMethod());
    }

    public static function post(string $url): self
    {
        return new self($url, new PostMethod());
    }

    public static function delete(string $url): self
    {
        return new self($url, new DeleteMethod());
    }

    public static function patch(string $url): self
    {
        return new self($url, new PatchMethod());
    }

    public static function put(string $url): self
    {
        return new self($url, new PutMethod());
    }

    /**
     * @param TagObject[] $tags
     *
     * @return self
     */
    public function tags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function routeAuthorization(RouteAuthorization $routeAuthorization): self
    {
        $this->routeAuthorization = $routeAuthorization;

        return $this;
    }

    public function rateLimit(RateLimitInterface $rateLimit): self
    {
        $this->rateLimit = $rateLimit;

        return $this;
    }

    /**
     * @param Order[] $orderings
     */
    public function ordering(array $orderings): self
    {
        $this->orderings = $orderings;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function summary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
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

    /**
     * @return Order[]
     */
    public function getOrderings(): array
    {
        return $this->orderings;
    }
}
