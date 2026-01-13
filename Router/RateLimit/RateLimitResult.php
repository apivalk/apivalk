<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\RateLimit;

class RateLimitResult
{
    /** @var int */
    private $limit;
    /** @var int */
    private $remaining;
    /** @var int|null */
    private $resetAt;
    /** @var string */
    private $name;
    /** @var int */
    private $windowSeconds;

    public function __construct(
        string $name,
        int $limit,
        int $remaining,
        int $windowSeconds,
        ?int $resetAt
    ) {
        $this->name = $name;
        $this->limit = $limit;
        $this->remaining = $remaining;
        $this->windowSeconds = $windowSeconds;
        $this->resetAt = $resetAt;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRemaining(): int
    {
        return $this->remaining;
    }

    public function getResetAt(): ?int
    {
        return $this->resetAt;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWindowSeconds(): int
    {
        return $this->windowSeconds;
    }

    /** @return array{X-RateLimit-Limit: int, X-RateLimit-Remaining: int, X-RateLimit-Reset: int, Retry-After?: int} */
    public function toHeaderArray(): array
    {
        $array = [
            'X-RateLimit-Limit' => $this->limit,
            'X-RateLimit-Remaining' => $this->remaining,
            'X-RateLimit-Reset' => $this->resetAt,
        ];

        if ($this->getRemaining() <= 0) {
            $array['Retry-After'] = $this->resetAt;
        }

        return $array;
    }
}
