<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\TooManyRequestsApivalkResponse;
use apivalk\apivalk\Router\RateLimit\RateLimitContext;
use apivalk\apivalk\Router\RateLimit\RateLimiter;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    /** @var CacheInterface */
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function process(
        ApivalkRequestInterface $request,
        AbstractApivalkController $controller,
        callable $next
    ): AbstractApivalkResponse {
        $route = $controller::getRoute();
        $rateLimit = $route->getRateLimit();

        if (!$rateLimit instanceof RateLimitInterface) {
            return $next($request);
        }

        $context = RateLimitContext::byRequest($route, $request);
        $rateLimiter = new RateLimiter($this->cache);

        $rateLimitResult = $rateLimiter->allow($rateLimit, $context);
        $request->setRateLimitResult($rateLimitResult);

        if ($rateLimitResult->getRemaining() <= 0) {
            return new TooManyRequestsApivalkResponse();
        }

        return $next($request);
    }
}
