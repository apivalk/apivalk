<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\RateLimit;

interface RateLimitInterface
{
    public function getName(): string;

    public function getMaxAttempts(): int;

    public function getWindowInSeconds(): int;

    public function getKey(RateLimitContext $rateLimitContext): string;
}
