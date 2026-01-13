<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\RateLimit;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;

class RateLimitContext
{
    /** @var string|null */
    private $ip;
    /** @var AbstractAuthIdentity */
    private $authIdentity;
    /** @var string */
    private $route;
    /** @var string */
    private $method;

    public function __construct(
        ?string $ip,
        AbstractAuthIdentity $authIdentity,
        string $route,
        string $method
    ) {
        $this->ip = $ip;
        $this->authIdentity = $authIdentity;
        $this->route = $route;
        $this->method = $method;
    }

    public static function byRequest(Route $route, ApivalkRequestInterface $abstractApivalkRequest): self
    {
        return new self(
            $abstractApivalkRequest->getIp(),
            $abstractApivalkRequest->getAuthIdentity(),
            $route->getUrl(),
            $abstractApivalkRequest->getMethod()->getName()
        );
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getAuthIdentity(): AbstractAuthIdentity
    {
        return $this->authIdentity;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
