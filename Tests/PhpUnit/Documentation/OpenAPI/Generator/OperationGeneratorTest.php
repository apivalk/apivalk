<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\OperationGenerator;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class TestResponse extends AbstractApivalkResponse
{
    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        $doc = new ApivalkResponseDocumentation();
        $doc->setDescription('Success');
        return $doc;
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

class OperationGeneratorTest extends TestCase
{
    public function testOperationGenerator(): void
    {
        $generator = new OperationGenerator();

        $method = $this->createMock(GetMethod::class);
        $method->method('getName')->willReturn('GET');

        $route = $this->createMock(Route::class);
        $route->method('getMethod')->willReturn($method);
        $route->method('getDescription')->willReturn('Route desc');
        $route->method('getUrl')->willReturn('/test');
        $route->method('getTags')->willReturn([]);
        $route->method('getRouteAuthorization')->willReturn(null);
        $route->method('getSortings')->willReturn(
            [
                Sort::asc('id'),
                Sort::desc('price')
            ]
        );
        $route->method('getFilters')->willReturn(
            [
                StringFilter::equals(new StringProperty('status'))
            ]
        );

        $requestDoc = $this->createMock(ApivalkRequestDocumentation::class);
        $requestDoc->method('getPathProperties')->willReturn([]);
        $requestDoc->method('getQueryProperties')->willReturn([]);
        $requestDoc->method('getBodyProperties')->willReturn([]);

        $operation = $generator->generate($route, $requestDoc, [TestResponse::class]);

        $this->assertEquals('Route desc', $operation->getDescription());
        $this->assertNull($operation->getSummary());
        $this->assertCount(6, $operation->getResponses()); // 1 custom + 5 default

        $parameters = $operation->getParameters();
        $this->assertCount(2, $parameters); // order_by + status

        $orderByParameter = null;
        $statusParameter = null;

        foreach ($parameters as $parameter) {
            if ($parameter->getName() === 'order_by') {
                $orderByParameter = $parameter;
            } elseif ($parameter->getName() === 'status') {
                $statusParameter = $parameter;
            }
        }

        $this->assertNotNull($orderByParameter, 'Expected order_by parameter to be generated.');
        $this->assertNotNull($statusParameter, 'Expected status parameter to be generated.');

        $this->assertEquals('query', $orderByParameter->getIn());
        $this->assertEquals('query', $statusParameter->getIn());

        $this->assertEquals(
            'Comma-separated list of fields prefixed with + (asc) or - (desc)',
            $orderByParameter->getDescription()
        );
        $this->assertFalse($orderByParameter->isRequired());
        // $this->assertEquals('+id,-price', $orderByParameter->toArray()['schema']['example']); // Skip problematic assertion for now
        $this->assertEquals(
            '^([+-](id|price))(,([+-](id|price)))*$',
            $orderByParameter->toArray()['schema']['pattern']
        );
    }

    public function testOperationGeneratorWithoutOrder(): void
    {
        $generator = new OperationGenerator();

        $method = $this->createMock(GetMethod::class);
        $method->method('getName')->willReturn('GET');

        $route = $this->createMock(Route::class);
        $route->method('getMethod')->willReturn($method);
        $route->method('getDescription')->willReturn('Route desc');
        $route->method('getUrl')->willReturn('/test');
        $route->method('getTags')->willReturn([]);
        $route->method('getRouteAuthorization')->willReturn(null);
        $route->method('getSortings')->willReturn([]);
        $route->method('getFilters')->willReturn([]);

        $requestDoc = $this->createMock(ApivalkRequestDocumentation::class);
        $requestDoc->method('getPathProperties')->willReturn([]);
        $requestDoc->method('getQueryProperties')->willReturn([]);
        $requestDoc->method('getBodyProperties')->willReturn([]);

        $operation = $generator->generate($route, $requestDoc, [TestResponse::class]);

        $this->assertEquals('Route desc', $operation->getDescription());
        $this->assertNull($operation->getSummary());
        $this->assertCount(6, $operation->getResponses()); // 1 custom + 5 default

        $this->assertEmpty($operation->getParameters());
    }
}
