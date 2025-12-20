<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Security\AbstractAuthIdentity;
use PHPUnit\Framework\TestCase;

abstract class AbstractApiControllerTest extends TestCase
{
    abstract public function getRequestClass(): string;

    abstract public function getExpectedRoute(): Route;

    abstract public function getController(): AbstractApivalkController;

    public function createRequest(array $data, ?AbstractAuthIdentity $authIdentity = null): ApivalkRequestInterface
    {
        $requestClass = $this->getRequestClass();

        /** @var ApivalkRequestInterface $request */
        $request = new $requestClass();

        if ($authIdentity !== null) {
            $request->setAuthIdentity($authIdentity);
        }

        $this->populateRequest($request, $data);

        return $request;
    }

    public function invoke(ApivalkRequestInterface $request, AbstractApivalkController $controller): AbstractApivalkResponse
    {
        return $controller($request);
    }

    public function testRoute(): void
    {
        $this->assertEquals($this->getExpectedRoute(), $this->getController()->getRoute());
        $this->assertInstanceOf(Route::class, $this->getController()->getRoute());
    }

    public function populateRequest(ApivalkRequestInterface $request, array $data): ApivalkRequestInterface
    {
        foreach ($data as $key => $value) {
            $request->{$key} = $value;
        }

        return $request;
    }
}
