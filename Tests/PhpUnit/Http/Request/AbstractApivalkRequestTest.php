<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request;

use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Filter\Operator;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use PHPUnit\Framework\TestCase;

class AbstractApivalkRequestTest extends TestCase
{
    public function testPopulateWithFilters(): void
    {
        $_GET['id'] = '123';
        
        $request = new class extends AbstractApivalkRequest {
            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }
        };

        $filter = new IntegerFilter(new IntegerProperty('id'), Operator::GT);
        $route = Route::get('/test')
            ->filtering([$filter]);

        $request->populate($route, new ApivalkRequestDocumentation());

        $this->assertSame(123, $request->filtering()->id->greaterThan);
        $this->assertInstanceOf(IntegerFilter::class, $request->filtering()->get('id'));
    }

    public function testGettersAndSetters(): void
    {
        $request = new class extends AbstractApivalkRequest {
            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }
        };

        $auth = new GuestAuthIdentity([]);
        $request->setAuthIdentity($auth);
        $this->assertInstanceOf(GuestAuthIdentity::class, $request->getAuthIdentity());
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
        $request->populate($route, new ApivalkRequestDocumentation());

        $this->assertSame($method, $request->getMethod());
        $this->assertInstanceOf(ParameterBag::class, $request->header());
        $this->assertInstanceOf(ParameterBag::class, $request->query());
        $this->assertInstanceOf(ParameterBag::class, $request->body());
        $this->assertInstanceOf(ParameterBag::class, $request->path());
        $this->assertInstanceOf(FileBag::class, $request->file());
        $this->assertInstanceOf(SortBag::class, $request->sorting());
        $this->assertInstanceOf(FilterBag::class, $request->filtering());
    }
}
