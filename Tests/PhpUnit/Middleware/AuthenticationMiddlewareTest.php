<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Middleware\AuthenticationMiddleware;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Parameter\Parameter;
use apivalk\apivalk\Security\Authenticator\AuthenticatorInterface;
use apivalk\apivalk\Security\AbstractAuthIdentity;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class AuthenticationMiddlewareTest extends TestCase
{
    public function testProcessWithValidToken(): void
    {
        $token = 'valid-token';
        $identity = $this->createMock(AbstractAuthIdentity::class);
        
        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $authenticator->expects($this->once())
            ->method('authenticate')
            ->with($token)
            ->willReturn($identity);

        $headerBag = new ParameterBag();
        $headerBag->set(new Parameter('Authorization', 'Bearer ' . $token));

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('header')->willReturn($headerBag);
        $request->expects($this->once())
            ->method('setAuthIdentity')
            ->with($identity);

        $middleware = new AuthenticationMiddleware($authenticator);
        
        $response = $this->createMock(AbstractApivalkResponse::class);
        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->process($request, 'SomeController', $next);
        $this->assertSame($response, $result);
    }

    public function testProcessWithNoAuthorizationHeader(): void
    {
        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $authenticator->expects($this->never())->method('authenticate');

        $headerBag = new ParameterBag();

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('header')->willReturn($headerBag);
        $request->expects($this->never())->method('setAuthIdentity');

        $middleware = new AuthenticationMiddleware($authenticator);
        
        $response = $this->createMock(AbstractApivalkResponse::class);
        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->process($request, 'SomeController', $next);
        $this->assertSame($response, $result);
    }

    public function testProcessWithInvalidToken(): void
    {
        $token = 'invalid-token';
        
        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $authenticator->expects($this->once())
            ->method('authenticate')
            ->with($token)
            ->willReturn(null);

        $headerBag = new ParameterBag();
        $headerBag->set(new Parameter('Authorization', 'Bearer ' . $token));

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('header')->willReturn($headerBag);
        $request->expects($this->never())->method('setAuthIdentity');

        $middleware = new AuthenticationMiddleware($authenticator);
        
        $response = $this->createMock(AbstractApivalkResponse::class);
        $next = function ($req) use ($response) {
            return $response;
        };

        $result = $middleware->process($request, 'SomeController', $next);
        $this->assertSame($response, $result);
    }
}
