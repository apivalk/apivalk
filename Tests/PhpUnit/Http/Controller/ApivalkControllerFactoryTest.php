<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Controller;

use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ApivalkControllerFactoryTest extends TestCase
{
    public function testCreateWithContainer(): void
    {
        $controller = $this->getMockBuilder(AbstractApivalkController::class)
            ->disableOriginalConstructor()
            ->addMethods(['__invoke'])
            ->getMockForAbstractClass();
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('MyController')->willReturn(true);
        $container->method('get')->with('MyController')->willReturn($controller);

        $factory = new ApivalkControllerFactory($container);
        $result = $factory->create('MyController');

        $this->assertSame($controller, $result);
    }

    public function testCreateRejectsAControllerWithoutInvoke(): void
    {
        $controller = $this->getMockForAbstractClass(AbstractApivalkController::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('NoInvokeController')->willReturn(true);
        $container->method('get')->with('NoInvokeController')->willReturn($controller);

        $factory = new ApivalkControllerFactory($container);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('has no __invoke() method');

        $factory->create('NoInvokeController');
    }

    public function testCreateWithoutContainer(): void
    {
        $factory = new ApivalkControllerFactory();
        
        // Use an anonymous class that exists
        $controllerClass = get_class(new class extends AbstractApivalkController {
            public static function getRoute(): Route { return new Route('/', new GetMethod()); }
            public static function getRequestClass(): string { return ''; }
            public static function getResponseClasses(): array { return []; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse { return $this->createMock(AbstractApivalkResponse::class); }
        });

        $result = $factory->create($controllerClass);
        $this->assertInstanceOf($controllerClass, $result);
    }

    public function testCreateNonExistentClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Controller class "NonExistent" does not exist');
        
        $factory = new ApivalkControllerFactory();
        $factory->create('NonExistent');
    }

    public function testCreateInvalidClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must extend AbstractApivalkController');
        
        $factory = new ApivalkControllerFactory();
        $factory->create(\stdClass::class);
    }
}
