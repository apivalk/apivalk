<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\UnauthorizedApivalkResponse;

class SecurityMiddleware implements MiddlewareInterface
{
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

        return $next($request);
    }
}
