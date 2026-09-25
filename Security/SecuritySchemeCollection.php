<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security;

use apivalk\apivalk\Documentation\OpenAPI\Object\SecuritySchemeObject;
use apivalk\apivalk\Router\AbstractRouter;

/**
 * The security schemes an API offers, keyed by their name.
 *
 * Single source for both sides: the SecurityMiddleware resolves a route's scheme here at runtime,
 * the OpenAPIGenerator renders the same schemes into `components.securitySchemes`.
 */
class SecuritySchemeCollection
{
    /** @var array<string, SecuritySchemeObject> */
    private array $schemes = [];

    public function add(SecuritySchemeObject $scheme): void
    {
        $name = $scheme->getName();

        if (isset($this->schemes[$name])) {
            throw new \LogicException(\sprintf('Security scheme "%s" is already registered.', $name));
        }

        $this->schemes[$name] = $scheme;
    }

    public function get(string $name): SecuritySchemeObject
    {
        if (!isset($this->schemes[$name])) {
            throw new \LogicException(\sprintf(
                'Unknown security scheme "%s". Registered: %s.',
                $name,
                $this->schemes === [] ? 'none' : '"' . \implode('", "', \array_keys($this->schemes)) . '"'
            ));
        }

        return $this->schemes[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->schemes[$name]);
    }

    /** @return array<string, SecuritySchemeObject> */
    public function all(): array
    {
        return $this->schemes;
    }

    public function assertRoutesResolvable(AbstractRouter $router): void
    {
        foreach ($router->getRoutes() as $entry) {
            $routeAuthorization = $entry['route']->getRouteAuthorization();

            if ($routeAuthorization === null || $this->has($routeAuthorization->getSecuritySchemeName())) {
                continue;
            }

            throw new \LogicException(
                \sprintf(
                    'Route "%s" requires the security scheme "%s", which is not registered.',
                    $entry['route']->getUrl(),
                    $routeAuthorization->getSecuritySchemeName()
                )
            );
        }
    }
}
