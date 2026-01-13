<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;

class Route
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
    /** @var null|RateLimitInterface */
    private $rateLimit;

    /**
     * @param string                      $url
     * @param MethodInterface             $method
     * @param string|null                 $description
     * @param TagObject[]                 $tags
     * @param SecurityRequirementObject[] $securityRequirements
     * @param RateLimitInterface|null     $rateLimit
     */
    public function __construct(
        string $url,
        MethodInterface $method,
        ?string $description = null,
        array $tags = [],
        array $securityRequirements = [],
        ?RateLimitInterface $rateLimit = null
    ) {
        $this->url = $url;
        $this->method = $method;
        $this->description = $description;
        $this->tags = $tags;
        $this->securityRequirements = $securityRequirements;
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

    /** @return SecurityRequirementObject[] */
    public function getSecurityRequirements(): array
    {
        return $this->securityRequirements;
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
