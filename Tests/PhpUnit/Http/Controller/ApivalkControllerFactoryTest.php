<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Controller;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Controller\ApivalkControllerFactory;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use Psr\Container\ContainerInterface;

class ApivalkControllerFactoryTest extends TestCase
{
    public function testCreateWithContainer(): void
    {
        $controller = $this->createMock(AbstractApivalkController::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('MyController')->willReturn(true);
        $container->method('get')->with('MyController')->willReturn($controller);

        $factory = new ApivalkControllerFactory($container);
        $result = $factory->create('MyController');

        $this->assertSame($controller, $result);
    }

    public function testCreateWithoutContainer(): void
    {
        $factory = new ApivalkControllerFactory();
        
        // Use an anonymous class that exists
        $controllerClass = get_class(new class extends AbstractApivalkController {
            public static function getRoute(): \apivalk\apivalk\Router\Route { return new \apivalk\apivalk\Router\Route('/', new \apivalk\apivalk\Http\Method\GetMethod()); }
            public static function getRequestClass(): string { return ''; }
            public static function getResponseClasses(): array { return []; }
            public function __invoke(\apivalk\apivalk\Http\Request\ApivalkRequestInterface $request): \apivalk\apivalk\Http\Response\AbstractApivalkResponse { return $this->createMock(\apivalk\apivalk\Http\Response\AbstractApivalkResponse::class); }
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
