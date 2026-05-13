<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Population\Strategy;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Method\MethodInterface;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\Strategy\MethodPopulationStrategy;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class MethodPopulationStrategyTest extends TestCase
{
    public function testSetsMethodFromRoute(): void
    {
        $route = Route::get('/api/v1/animals');

        $request = $this->makeRequest();
        $strategy = new MethodPopulationStrategy();
        $strategy->populate($request, new RequestPopulationContext($route, new ApivalkRequestDocumentation()));

        self::assertInstanceOf(MethodInterface::class, $request->getMethod());
        self::assertSame(\get_class($route->getMethod()), \get_class($request->getMethod()));
    }

    private function makeRequest(): AbstractApivalkRequest
    {
        return new class extends AbstractApivalkRequest {
            /** @var MethodInterface|null */
            private $method;

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }

            public function setMethod(MethodInterface $method): void
            {
                $this->method = $method;
            }

            public function getMethod(): MethodInterface
            {
                return $this->method;
            }
        };
    }
}
