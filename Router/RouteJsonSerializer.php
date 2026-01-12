<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecurityRequirementObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Http\Method\MethodFactory;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Security\Scope;

class RouteJsonSerializer
{
    /**
     * @return array{
     *     url: string,
     *     method: string,
     *     description: string|null,
     *     tags: array<int, array{
     *         name: string,
     *         description: string|null
     *     }>,
     *     securityRequirements: array<int, array{
     *         name: string,
     *         scopes: array<int, array{
     *             name: string,
     *             description: string|null
     *         }>
     *     }>,
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

        $securityRequirements = [];
        foreach ($route->getSecurityRequirements() as $securityRequirement) {
            $scopes = [];

            foreach ($securityRequirement->getScopes() as $scope) {
                $scopes[] = ['name' => $scope->getName(), 'description' => $scope->getDescription()];
            }

            $securityRequirements[] = ['name' => $securityRequirement->getName(), 'scopes' => $scopes];
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
            'tags' => $tags,
            'securityRequirements' => $securityRequirements,
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

        $securityRequirements = [];
        foreach ($jsonArray['securityRequirements'] ?? [] as $securityRequirement) {
            $scopes = [];

            foreach ($securityRequirement['scopes'] as $scope) {
                if (\is_string($scope)) {
                    $scopes[] = new Scope($scope);
                } else {
                    $scopes[] = new Scope($scope['name'], $scope['description'] ?? null);
                }
            }

            $securityRequirements[] = new SecurityRequirementObject($securityRequirement['name'], $scopes);
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
            $tags,
            $securityRequirements,
            $rateLimit
        );
    }
}
