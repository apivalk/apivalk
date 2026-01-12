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
        if (!$request->header()->has('Authorization')) {
            return $next($request);
        }

        $authorization = $request->header()->get('Authorization')->getValue();
        
        if ($authorization && preg_match('/Bearer\s+(.*)$/i', (string)$authorization, $matches)) {
            $token = $matches[1];
            $identity = $this->authenticator->authenticate($token);
            
            if ($identity) {
                $request->setAuthIdentity($identity);
            }
        }

        return $next($request);
    }
}
