<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Security\Authenticator\AuthenticatorInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /** @var AuthenticatorInterface */
    private $authenticator;

    public function __construct(AuthenticatorInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function process(
        ApivalkRequestInterface $request,
        AbstractApivalkController $controller,
        callable $next
    ): AbstractApivalkResponse {
        $authorizationValue = null;

        if ($request->header()->has('Authorization')) {
            $authorizationValue = $request->header()->get('Authorization')->getValue();
        }

        if ($request->header()->has('AUTHORIZATION')) {
            $authorizationValue = $request->header()->get('AUTHORIZATION')->getValue();
        }

        if ($request->header()->has('authorization')) {
            $authorizationValue = $request->header()->get('authorization')->getValue();
        }

        if ($authorizationValue === null || $authorizationValue === '') {
            return $next($request);
        }

        if (preg_match('/Bearer\s+(.*)$/i', (string)$authorizationValue, $matches)) {
            $bearerToken = $matches[1];
            $identity = $this->authenticator->authenticate($bearerToken);

            if ($identity) {
                $request->setAuthIdentity($identity);
            }
        }

        return $next($request);
    }
}
