<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Controller;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\Response\ControllerResponseScanner;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\RequestClassResolver;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class AbstractApivalkControllerTest extends TestCase
{
    public function testControllerDeclaresItsRouteAndIsInvokable(): void
    {
        $controller = new ControllerTestController();

        $this->assertInstanceOf(Route::class, $controller::getRoute());
        $this->assertInstanceOf(AbstractApivalkResponse::class, $controller(new ControllerTestRequest()));
    }

    public function testRequestAndResponseClassesComeFromTheInvokeSignature(): void
    {
        $this->assertSame(
            ControllerTestRequest::class,
            RequestClassResolver::resolve(ControllerTestController::class)
        );

        $this->assertSame(
            [ControllerTestResponse::class],
            ControllerResponseScanner::scan(ControllerTestController::class)
        );
    }

    /**
     * An interface names nothing to instantiate, so the request the route fills in entirely is used.
     */
    public function testAnInterfaceParameterFallsBackToResourceRequest(): void
    {
        $this->assertSame(
            ResourceRequest::class,
            RequestClassResolver::resolve(WideControllerTestController::class)
        );
    }
}

class ControllerTestRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}

class ControllerTestResponse extends AbstractApivalkResponse
{
    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        return new ApivalkResponseDocumentation();
    }

    public static function getStatusCode(): int
    {
        return 200;
    }

    public function toArray(): array
    {
        return [];
    }
}

class ControllerTestController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/', new GetMethod());
    }

    public function __invoke(ControllerTestRequest $request): AbstractApivalkResponse
    {
        return new ControllerTestResponse();
    }
}

class WideControllerTestController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/wide', new GetMethod());
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        return new ControllerTestResponse();
    }
}
