<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Router\Route;
use apivalk\apivalk\Security\AbstractAuthIdentity;

class AbstractApivalkRequestTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $request = new class extends AbstractApivalkRequest {
            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }
        };

        $this->assertNull($request->getAuthIdentity());
        $auth = $this->createMock(AbstractAuthIdentity::class);
        $request->setAuthIdentity($auth);
        $this->assertSame($auth, $request->getAuthIdentity());
    }

    public function testPopulate(): void
    {
        $request = new class extends AbstractApivalkRequest {
            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }
        };

        $method = $this->createMock(MethodInterface::class);
        $route = $this->createMock(Route::class);
        $route->method('getMethod')->willReturn($method);
        
        // Mock global factories is hard, but we can check if they are called and set bags
        $request->populate($route);

        $this->assertSame($method, $request->getMethod());
        $this->assertInstanceOf(\apivalk\apivalk\Http\Request\Parameter\ParameterBag::class, $request->header());
        $this->assertInstanceOf(\apivalk\apivalk\Http\Request\Parameter\ParameterBag::class, $request->query());
        $this->assertInstanceOf(\apivalk\apivalk\Http\Request\Parameter\ParameterBag::class, $request->body());
        $this->assertInstanceOf(\apivalk\apivalk\Http\Request\Parameter\ParameterBag::class, $request->path());
        $this->assertInstanceOf(\apivalk\apivalk\Http\Request\File\FileBag::class, $request->file());
    }
}
