<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\RateLimit;

class IpRateLimit implements RateLimitInterface
{
    /** @var string */
    private $name;
    /** @var int */
    private $maxAttempts;
    /** @var int */
    private $windowInSeconds;

    public function __construct(
        string $name,
        int $maxAttempts,
        int $windowInSeconds
    ) {
        $this->name = $name;
        $this->maxAttempts = $maxAttempts;
        $this->windowInSeconds = $windowInSeconds;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getWindowInSeconds(): int
    {
        return $this->windowInSeconds;
    }

    public function getKey(RateLimitContext $rateLimitContext): string
    {
        return \sprintf('rateLimit:%s:%s', $this->name, $rateLimitContext->getIp());
    }
}
