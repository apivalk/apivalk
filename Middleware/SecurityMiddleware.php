<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\UnauthorizedApivalkResponse;
use apivalk\apivalk\Security\SecuritySchemeCollection;

class SecurityMiddleware implements MiddlewareInterface
{
    private SecuritySchemeCollection $securitySchemes;

    public function __construct(SecuritySchemeCollection $securitySchemes)
    {
        $this->securitySchemes = $securitySchemes;
    }

    public function process(
        ApivalkRequestInterface $request,
        AbstractApivalkController $controller,
        callable $next
    ): AbstractApivalkResponse {
        $routeAuthorization = $controller::getRoute()->getRouteAuthorization();

        if ($routeAuthorization === null) {
            return $next($request);
        }

        $authIdentity = $request->getAuthIdentity();

        $audiences = $this->securitySchemes->get($routeAuthorization->getSecuritySchemeName())->getAudiences();

        // A token issued for another audience is not valid for this API at all (RFC 6750
        // invalid_token), so it is rejected before scopes turn a foreign token into a 403.
        if ($audiences !== [] && !$authIdentity->isAnyAudGranted($audiences)) {
            return new UnauthorizedApivalkResponse();
        }

        foreach ($routeAuthorization->getRequiredScopes() as $requiredScope) {
            if (!$authIdentity->isScopeGranted($requiredScope)) {
                if ($authIdentity->isAuthenticated()) {
                    return new ForbiddenApivalkResponse();
                }

                return new UnauthorizedApivalkResponse();
            }
        }

        foreach ($routeAuthorization->getRequiredPermissions() as $requiredPermission) {
            if (!$authIdentity->isPermissionGranted($requiredPermission)) {
                if ($authIdentity->isAuthenticated()) {
                    return new ForbiddenApivalkResponse();
                }

                return new UnauthorizedApivalkResponse();
            }
        }

        if (!$authIdentity->isAuthenticated()) {
            return new UnauthorizedApivalkResponse();
        }

        return $next($request);
    }
}
