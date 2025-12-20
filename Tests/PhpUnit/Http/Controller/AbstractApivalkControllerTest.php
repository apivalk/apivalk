<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Controller;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\Route;

class AbstractApivalkControllerTest extends TestCase
{
    public function testController(): void
    {
        $controller = new class extends AbstractApivalkController {
            public static function getRoute(): Route { return new Route('/', new \apivalk\apivalk\Http\Method\GetMethod()); }
            public static function getRequestClass(): string { return 'RequestClass'; }
            public static function getResponseClasses(): array { return ['ResponseClass']; }
            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse 
            {
                 return new class extends AbstractApivalkResponse {
                     public static function getDocumentation(): \apivalk\apivalk\Documentation\ApivalkResponseDocumentation { return new \apivalk\apivalk\Documentation\ApivalkResponseDocumentation(); }
                     public static function getStatusCode(): int { return 200; }
                     public function toArray(): array { return []; }
                 };
            }
        };

        $this->assertInstanceOf(Route::class, $controller::getRoute());
        $this->assertEquals('RequestClass', $controller::getRequestClass());
        $this->assertEquals(['ResponseClass'], $controller::getResponseClasses());
        
        $request = $this->createMock(ApivalkRequestInterface::class);
        $this->assertInstanceOf(AbstractApivalkResponse::class, $controller($request));
    }
}
