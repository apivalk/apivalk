<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\UnauthorizedApivalkResponse;

class SecurityMiddleware implements MiddlewareInterface
{
    public function process(
        ApivalkRequestInterface $request,
        string $controllerClass,
        callable $next
    ): AbstractApivalkResponse {
        /** @var AbstractApivalkController $controllerClass */
        $route = $controllerClass::getRoute();
        $securityRequirements = $route->getSecurityRequirements();

        if (empty($securityRequirements)) {
            return $next($request);
        }

        $authIdentity = $request->getAuthIdentity();
        $isAuthorized = false;

        foreach ($securityRequirements as $requirement) {
            if ($requirement->isPublicEndpoint()) {
                $isAuthorized = true;
                break;
            }

            $requiredScopes = $requirement->getScopes();

            $hasAllScopes = true;
            foreach ($requiredScopes as $requiredScope) {
                if (!$authIdentity->isScopeGranted($requiredScope)) {
                    $hasAllScopes = false;
                    break;
                }
            }

            if ($hasAllScopes) {
                $isAuthorized = true;
                break;
            }
        }

        if (!$isAuthorized) {
            if (!$authIdentity->isAuthenticated()) {
                return new UnauthorizedApivalkResponse();
            }

            return new ForbiddenApivalkResponse();
        }

        return $next($request);
    }
}
