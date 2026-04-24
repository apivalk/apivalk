<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\OperationGenerator;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\Sort;
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
    private function createRouteMock(array $overrides = []): Route
    {
        $method = $this->createMock(GetMethod::class);
        $method->method('getName')->willReturn('GET');

        $route = $this->createMock(Route::class);
        $route->method('getMethod')->willReturn($overrides['method'] ?? $method);
        $route->method('getDescription')->willReturn($overrides['description'] ?? 'Route desc');
        $route->method('getUrl')->willReturn($overrides['url'] ?? '/test');
        $route->method('getTags')->willReturn($overrides['tags'] ?? []);
        $route->method('getRouteAuthorization')->willReturn($overrides['routeAuthorization'] ?? null);
        $route->method('getSortings')->willReturn($overrides['sortings'] ?? []);
        $route->method('getFilters')->willReturn($overrides['filters'] ?? []);
        $route->method('getPagination')->willReturn($overrides['pagination'] ?? null);
        $route->method('getRateLimit')->willReturn($overrides['rateLimit'] ?? null);
        $route->method('getSummary')->willReturn($overrides['summary'] ?? null);

        return $route;
    }

    private function createRequestDocMock(): ApivalkRequestDocumentation
    {
        $requestDoc = $this->createMock(ApivalkRequestDocumentation::class);
        $requestDoc->method('getPathProperties')->willReturn([]);
        $requestDoc->method('getQueryProperties')->willReturn([]);
        $requestDoc->method('getBodyProperties')->willReturn([]);

        return $requestDoc;
    }

    public function testOperationGenerator(): void
    {
        $generator = new OperationGenerator();

        $route = $this->createRouteMock([
            'sortings' => [Sort::asc('id'), Sort::desc('price')],
            'filters' => [StringFilter::equals(new StringProperty('status'))],
        ]);

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $this->assertEquals('Route desc', $operation->getDescription());
        $this->assertNull($operation->getSummary());
        $this->assertCount(6, $operation->getResponses()); // 1 custom + 5 default

        $parameters = $operation->getParameters();
        // order_by + status + Accept-Language
        $this->assertCount(3, $parameters);

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
        $this->assertEquals(
            '/^([+-](id|price))(,([+-](id|price)))*$/',
            $orderByParameter->toArray()['schema']['pattern']
        );
    }

    public function testOperationGeneratorWithoutOrder(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $this->assertEquals('Route desc', $operation->getDescription());
        $this->assertNull($operation->getSummary());
        $this->assertCount(6, $operation->getResponses()); // 1 custom + 5 default

        // Only Accept-Language header parameter
        $this->assertCount(1, $operation->getParameters());
        $this->assertEquals('Accept-Language', $operation->getParameters()[0]->getName());
    }

    public function testAcceptLanguageHeaderParameterIsAlwaysPresent(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $acceptLanguageParam = null;
        foreach ($operation->getParameters() as $parameter) {
            if ($parameter->getName() === 'Accept-Language') {
                $acceptLanguageParam = $parameter;
            }
        }

        $this->assertNotNull($acceptLanguageParam, 'Expected Accept-Language parameter to be generated.');
        $this->assertEquals('header', $acceptLanguageParam->getIn());
        $this->assertFalse($acceptLanguageParam->isRequired());
        $this->assertStringContainsString('BCP 47', $acceptLanguageParam->getDescription());
    }

    public function testContentLanguageHeaderIsAlwaysPresentInResponses(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        foreach ($operation->getResponses() as $response) {
            $headers = $response->getHeaders();
            $this->assertArrayHasKey('Content-Language', $headers, \sprintf(
                'Expected Content-Language header in response %d.',
                $response->getStatusCode()
            ));
        }
    }

    public function testRateLimitHeadersNotPresentWhenNoRateLimit(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        foreach ($operation->getResponses() as $response) {
            $headers = $response->getHeaders();
            $this->assertArrayNotHasKey('X-RateLimit-Limit', $headers);
            $this->assertArrayNotHasKey('X-RateLimit-Remaining', $headers);
            $this->assertArrayNotHasKey('X-RateLimit-Reset', $headers);
            $this->assertArrayNotHasKey('Retry-After', $headers);
        }
    }

    public function testRateLimitHeadersPresentWhenRateLimitApplied(): void
    {
        $generator = new OperationGenerator();

        $rateLimit = $this->createMock(RateLimitInterface::class);
        $rateLimit->method('getWindowInSeconds')->willReturn(60);
        $rateLimit->method('getMaxAttempts')->willReturn(100);

        $route = $this->createRouteMock(['rateLimit' => $rateLimit]);

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        foreach ($operation->getResponses() as $response) {
            $headers = $response->getHeaders();
            $this->assertArrayHasKey('X-RateLimit-Limit', $headers, \sprintf(
                'Expected X-RateLimit-Limit header in response %d.',
                $response->getStatusCode()
            ));
            $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
            $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
            $this->assertArrayHasKey('Retry-After', $headers);
            $this->assertArrayHasKey('Content-Language', $headers);
        }
    }

    public function testRateLimitHeaderDescriptionIncludesWindowSeconds(): void
    {
        $generator = new OperationGenerator();

        $rateLimit = $this->createMock(RateLimitInterface::class);
        $rateLimit->method('getWindowInSeconds')->willReturn(120);
        $rateLimit->method('getMaxAttempts')->willReturn(50);

        $route = $this->createRouteMock(['rateLimit' => $rateLimit]);

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $firstResponse = $operation->getResponses()[0];
        $rateLimitHeader = $firstResponse->getHeaders()['X-RateLimit-Limit'];

        $this->assertStringContainsString('120 seconds', $rateLimitHeader->getDescription());
    }

    public function testResponseHeadersSerializeCorrectlyInToArray(): void
    {
        $generator = new OperationGenerator();

        $rateLimit = $this->createMock(RateLimitInterface::class);
        $rateLimit->method('getWindowInSeconds')->willReturn(60);
        $rateLimit->method('getMaxAttempts')->willReturn(100);

        $route = $this->createRouteMock(['rateLimit' => $rateLimit]);

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $firstResponse = $operation->getResponses()[0];
        $responseArray = $firstResponse->toArray();

        $statusCode = $firstResponse->getStatusCode();
        $this->assertArrayHasKey('headers', $responseArray[$statusCode]);

        $headersArray = $responseArray[$statusCode]['headers'];
        $this->assertArrayHasKey('Content-Language', $headersArray);
        $this->assertArrayHasKey('description', $headersArray['Content-Language']);
        $this->assertArrayHasKey('required', $headersArray['Content-Language']);

        $this->assertArrayHasKey('X-RateLimit-Limit', $headersArray);
        $this->assertArrayHasKey('description', $headersArray['X-RateLimit-Limit']);
    }

    public function testLocaleHeadersDisabled(): void
    {
        $generator = new OperationGenerator(false);
        $route = $this->createRouteMock();

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        foreach ($operation->getParameters() as $parameter) {
            $this->assertNotEquals(
                'Accept-Language',
                $parameter->getName(),
                'Accept-Language parameter should not be present when locale headers are disabled.'
            );
        }

        foreach ($operation->getResponses() as $response) {
            $this->assertArrayNotHasKey('Content-Language', $response->getHeaders());
        }
    }

    public function testLocaleHeadersDisabledStillIncludesRateLimitHeaders(): void
    {
        $rateLimit = $this->createMock(RateLimitInterface::class);
        $rateLimit->method('getWindowInSeconds')->willReturn(60);
        $rateLimit->method('getMaxAttempts')->willReturn(100);

        $generator = new OperationGenerator(false);
        $route = $this->createRouteMock(['rateLimit' => $rateLimit]);

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        foreach ($operation->getResponses() as $response) {
            $headers = $response->getHeaders();
            $this->assertArrayNotHasKey('Content-Language', $headers);
            $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
            $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
            $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
            $this->assertArrayHasKey('Retry-After', $headers);
        }
    }

    public function testLocaleHeadersEnabledByDefault(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $hasAcceptLanguage = false;
        foreach ($operation->getParameters() as $parameter) {
            if ($parameter->getName() === 'Accept-Language') {
                $hasAcceptLanguage = true;
            }
        }

        $this->assertTrue($hasAcceptLanguage, 'Accept-Language should be present by default.');

        foreach ($operation->getResponses() as $response) {
            $this->assertArrayHasKey('Content-Language', $response->getHeaders());
        }
    }

    public function testGenerateFromDocumentationProducesEquivalentOperation(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $documentations = [[
            'statusCode' => TestResponse::getStatusCode(),
            'documentation' => TestResponse::getDocumentation(),
        ]];

        $operation = $generator->generateFromDocumentation(
            $route,
            $this->createRequestDocMock(),
            $documentations
        );

        $this->assertEquals('Route desc', $operation->getDescription());
        $this->assertCount(6, $operation->getResponses()); // 1 custom + 5 default
    }

    public function testGenerateFromDocumentationSupportsMultipleResponseDocumentations(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $createDoc = new ApivalkResponseDocumentation();
        $createDoc->setDescription('Created');

        $viewDoc = new ApivalkResponseDocumentation();
        $viewDoc->setDescription('Viewed');

        $documentations = [
            ['statusCode' => 201, 'documentation' => $createDoc],
            ['statusCode' => 200, 'documentation' => $viewDoc],
        ];

        $operation = $generator->generateFromDocumentation(
            $route,
            $this->createRequestDocMock(),
            $documentations
        );

        $statusCodes = [];
        foreach ($operation->getResponses() as $response) {
            $statusCodes[] = $response->getStatusCode();
        }

        $this->assertContains(200, $statusCodes);
        $this->assertContains(201, $statusCodes);
    }

    public function testGenerateFromDocumentationStillEmitsAcceptLanguageParameter(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generateFromDocumentation(
            $route,
            $this->createRequestDocMock(),
            []
        );

        $names = [];
        foreach ($operation->getParameters() as $parameter) {
            $names[] = $parameter->getName();
        }

        $this->assertContains('Accept-Language', $names);
    }

    public function testGenerateFromDocumentationWithoutCustomResponsesStillIncludesDefaults(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generateFromDocumentation(
            $route,
            $this->createRequestDocMock(),
            []
        );

        // 0 custom + 5 default
        $this->assertCount(5, $operation->getResponses());
    }
}
