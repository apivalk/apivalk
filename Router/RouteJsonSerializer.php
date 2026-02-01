<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Http\Method\MethodFactory;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Security\RouteAuthorization;

class RouteJsonSerializer
{
    /**
     * @return array{
     *     url: string,
     *     method: string,
     *     description: string|null,
     *     summary: string|null,
     *     tags: array<int, array{
     *         name: string,
     *         description: string|null
     *     }>,
     *     routeAuthorization: array{
     *         securitySchemeName: string,
     *         scopes: string[],
     *         permissions: string[]
     *     }|null,
     *     rateLimit: array{
     *          class: class-string<RateLimitInterface>,
     *          name: string,
     *          maxAttempts: int,
     *          windowSeconds: int
     *      }|null
     * }
     */
    public static function serialize(Route $route): array
    {
        $tags = [];
        foreach ($route->getTags() as $tag) {
            $tags[] = ['name' => $tag->getName(), 'description' => $tag->getDescription()];
        }

        $routeAuthorization = $route->getRouteAuthorization();
        if ($routeAuthorization instanceof RouteAuthorization) {
            $routeAuthorizationData = [
                'securitySchemeName' => $routeAuthorization->getSecuritySchemeName(),
                'scopes' => $routeAuthorization->getRequiredScopes(),
                'permissions' => $routeAuthorization->getRequiredPermissions(),
            ];
        }

        $rateLimit = $route->getRateLimit();
        if ($rateLimit instanceof RateLimitInterface) {
            $rateLimitData = [
                'class' => \get_class($rateLimit),
                'name' => $rateLimit->getName(),
                'maxAttempts' => $rateLimit->getMaxAttempts(),
                'windowSeconds' => $rateLimit->getWindowInSeconds(),
            ];
        }

        return [
            'url' => $route->getUrl(),
            'method' => $route->getMethod()->getName(),
            'description' => $route->getDescription(),
            'summary' => $route->getSummary(),
            'tags' => $tags,
            'routeAuthorization' => $routeAuthorizationData ?? null,
            'rateLimit' => $rateLimitData ?? null,
        ];
    }

    /** @param string $json should contain JSON in the format returned by RouteJsonSerializer::serialize */
    public static function deserialize(string $json): Route
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

        $routeAuthorization = null;
        $routeAuthorizationData = $jsonArray['routeAuthorization'] ?? null;
        if ($routeAuthorizationData !== null) {
            $routeAuthorization = new RouteAuthorization(
                $routeAuthorizationData['securitySchemeName'],
                $routeAuthorizationData['scopes'],
                $routeAuthorizationData['permissions']
            );
        }

        $rateLimit = null;
        $rateLimitData = $jsonArray['rateLimit'] ?? null;
        if (($rateLimitData !== null)
            && \class_exists($rateLimitData['class'])) {
            $rateLimit = new $rateLimitData['class'](
                $rateLimitData['name'],
                $rateLimitData['maxAttempts'],
                $rateLimitData['windowSeconds']
            );
        }

        return new Route(
            $jsonArray['url'],
            MethodFactory::create($jsonArray['method']),
            $jsonArray['description'] ?? null,
            $jsonArray['summary'] ?? null,
            $tags,
            $routeAuthorization,
            $rateLimit
        );
    }
}
