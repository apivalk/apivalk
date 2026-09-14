<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\RequestClassResolver;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class RequestClassResolverTest extends TestCase
{
    public function testAConcreteParameterTypeIsTheRequestClass(): void
    {
        $this->assertSame(
            ResolverRequest::class,
            RequestClassResolver::resolve(ConcreteRequestController::class)
        );
    }

    /**
     * Documented as a legitimate style for a controller that does not want its own request class.
     * An interface cannot be instantiated, so the request the route fills in entirely is used.
     */
    public function testAnInterfaceParameterFallsBackToResourceRequest(): void
    {
        $this->assertSame(
            ResourceRequest::class,
            RequestClassResolver::resolve(InterfaceRequestController::class)
        );
    }

    public function testAnAbstractParameterFallsBackToResourceRequest(): void
    {
        $this->assertSame(
            ResourceRequest::class,
            RequestClassResolver::resolve(AbstractRequestController::class)
        );
    }

    public function testAnUntypedParameterFallsBackToResourceRequest(): void
    {
        $this->assertSame(
            ResourceRequest::class,
            RequestClassResolver::resolve(UntypedRequestController::class)
        );
    }

    public function testASubclassOfResourceRequestIsKept(): void
    {
        $this->assertSame(
            ResolverResourceRequest::class,
            RequestClassResolver::resolve(ResourceSubclassController::class)
        );
    }

    public function testAControllerWithoutParametersFailsLoudly(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('takes no parameter');

        RequestClassResolver::resolve(NoParameterController::class);
    }

    public function testABuiltinParameterTypeFailsLoudly(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('has to be a class');

        RequestClassResolver::resolve(BuiltinParameterController::class);
    }

    public function testAParameterThatIsNotARequestFailsLoudly(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('does not implement');

        RequestClassResolver::resolve(WrongParameterController::class);
    }
}

class ResolverRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}

class ResolverResourceRequest extends ResourceRequest
{
}

abstract class AbstractResolverRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}

abstract class ResolverControllerStub extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/resolver', new GetMethod());
    }
}

class ConcreteRequestController extends ResolverControllerStub
{
    public function __invoke(ResolverRequest $request): AbstractApivalkResponse
    {
        return new NotFoundApivalkResponse();
    }
}

class InterfaceRequestController extends ResolverControllerStub
{
    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        return new NotFoundApivalkResponse();
    }
}

class AbstractRequestController extends ResolverControllerStub
{
    public function __invoke(AbstractResolverRequest $request): AbstractApivalkResponse
    {
        return new NotFoundApivalkResponse();
    }
}

class UntypedRequestController extends ResolverControllerStub
{
    public function __invoke($request): AbstractApivalkResponse
    {
        return new NotFoundApivalkResponse();
    }
}

class ResourceSubclassController extends ResolverControllerStub
{
    public function __invoke(ResolverResourceRequest $request): AbstractApivalkResponse
    {
        return new NotFoundApivalkResponse();
    }
}

class NoParameterController extends ResolverControllerStub
{
    public function __invoke(): AbstractApivalkResponse
    {
        return new NotFoundApivalkResponse();
    }
}

class BuiltinParameterController extends ResolverControllerStub
{
    public function __invoke(string $request): AbstractApivalkResponse
    {
        return new NotFoundApivalkResponse();
    }
}

class WrongParameterController extends ResolverControllerStub
{
    public function __invoke(Route $request): AbstractApivalkResponse
    {
        return new NotFoundApivalkResponse();
    }
}
