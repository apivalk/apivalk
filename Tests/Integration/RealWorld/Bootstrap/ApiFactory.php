<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Bootstrap;

use apivalk\apivalk\Apivalk;
use apivalk\apivalk\ApivalkConfiguration;
use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Middleware\AuthenticationMiddleware;
use apivalk\apivalk\Middleware\RateLimitMiddleware;
use apivalk\apivalk\Middleware\RequestValidationMiddleware;

use apivalk\apivalk\Middleware\SecurityMiddleware;
use apivalk\apivalk\Router\Router;
use apivalk\apivalk\Security\Authenticator\AuthenticatorInterface;
use apivalk\apivalk\Util\ClassLocator;

class ApiFactory
{
    public static function create(
        ?CacheInterface $cache = null,
        ?AuthenticatorInterface $authenticator = null,
        int $rateLimitMax = 1000,
        int $rateLimitWindow = 60
    ): Apivalk {
        $cache = $cache ?? new InMemoryCache();
        $authenticator = $authenticator ?? new TestAuthenticator();

        $classLocator = new ClassLocator(
            __DIR__ . '/../',
            'Tests\\Integration\\RealWorld'
        );

        $router = new Router($classLocator, $cache);

        $config = new ApivalkConfiguration($router);
        $stack = $config->getMiddlewareStack();

        $stack->add(new AuthenticationMiddleware($authenticator));
        $stack->add(new SecurityMiddleware());
        $stack->add(new RateLimitMiddleware($cache));
        $stack->add(new RequestValidationMiddleware());

        return new Apivalk($config);
    }
}
