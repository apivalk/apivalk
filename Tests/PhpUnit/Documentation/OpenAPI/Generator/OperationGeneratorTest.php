<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Generator;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\OpenAPI\Generator\OperationGenerator;
use apivalk\apivalk\Documentation\OpenAPI\Object\OperationObject;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\RateLimit\RateLimitInterface;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Filter\Operator;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Security\RouteAuthorization;
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
        $route->method('getPathProperties')->willReturn($overrides['pathProperties'] ?? []);

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
            'filters' => [new StringFilter(new StringProperty('status'), Operator::EQ)],
        ]);

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $this->assertEquals('Route desc', $operation->getDescription());
        $this->assertNull($operation->getSummary());
        // 200 from the controller, 405 always, 422 because the route declares sortings and filters
        $this->assertCount(3, $operation->getResponses());

        $parameters = $operation->getParameters();
        // order_by + filter + Accept-Language
        $this->assertCount(3, $parameters);

        $orderByParameter = null;
        $filterParameter  = null;

        foreach ($parameters as $parameter) {
            if ($parameter->getName() === 'order_by') {
                $orderByParameter = $parameter;
            } elseif ($parameter->getName() === 'status') {
                $filterParameter = $parameter;
            }
        }

        $this->assertNotNull($orderByParameter, 'Expected order_by parameter to be generated.');
        $this->assertNotNull($filterParameter, 'Expected a query parameter for the status filter.');

        $this->assertEquals('query', $orderByParameter->getIn());
        $this->assertEquals('query', $filterParameter->getIn());

        $this->assertEquals(
            'Comma-separated list of fields prefixed with + (asc) or - (desc)',
            $orderByParameter->getDescription()
        );
        $this->assertFalse($orderByParameter->isRequired());
        $this->assertEquals(
            '^([+-](id|price))(,([+-](id|price)))*$',
            $orderByParameter->toArray()['schema']['pattern']
        );

        // a field with a single operator is documented flat, with the operator spelled out
        $filterArray = $filterParameter->toArray();
        $this->assertArrayNotHasKey('style', $filterArray);
        $this->assertArrayNotHasKey('explode', $filterArray);
        $this->assertFalse($filterParameter->isRequired());
        $this->assertEquals('string', $filterArray['schema']['type']);
        $this->assertEquals('Equals filtering on `status`.', $filterArray['description']);
    }

    public function testOperationGeneratorDocumentsAMultiOperatorFilterAsADeepObject(): void
    {
        $generator = new OperationGenerator();

        $route = $this->createRouteMock([
            'filters' => [new StringFilter(new StringProperty('status'), Operator::EQ, Operator::NEQ)],
        ]);

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $filterParameter = null;
        foreach ($operation->getParameters() as $parameter) {
            if ($parameter->getName() === 'status') {
                $filterParameter = $parameter;
            }
        }

        $this->assertNotNull($filterParameter, 'Expected a deepObject parameter for the status filter.');

        $filterArray = $filterParameter->toArray();
        $this->assertEquals('deepObject', $filterArray['style']);
        $this->assertTrue($filterArray['explode']);
        $this->assertSame(['eq', 'neq'], array_keys($filterArray['schema']['properties']));
        $this->assertEquals('string', $filterArray['schema']['properties']['eq']['type']);
        $this->assertFalse($filterArray['schema']['additionalProperties']);
    }

    /**
     * A single-operator field is flat either way, so the flag is only observable on a field
     * that would otherwise be a deepObject.
     */
    public function testOperationGeneratorWithFlatFilters(): void
    {
        $generator = new OperationGenerator(true, true); // flatFilters=true

        $route = $this->createRouteMock([
            'filters' => [new IntegerFilter(new IntegerProperty('price', 'Price'), Operator::GT, Operator::LT)],
        ]);

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $parameters = $operation->getParameters();
        // price (flat) + Accept-Language, no order_by since no sortings
        $this->assertCount(2, $parameters);

        $names = array_map(static fn($p) => $p->getName(), $parameters);
        $this->assertContains('price', $names);
        $this->assertNotContains('filter', $names);

        foreach ($parameters as $parameter) {
            if ($parameter->getName() !== 'price') {
                continue;
            }

            $this->assertEquals('query', $parameter->getIn());

            $array = $parameter->toArray();
            $this->assertArrayNotHasKey('style', $array);
            $this->assertArrayNotHasKey('explode', $array);
            $this->assertEquals('integer', $array['schema']['type']);
            $this->assertArrayNotHasKey('properties', $array['schema']);
            // flatFilters drops the operator entirely, so it carries no operator hint either
            $this->assertEquals('Price', $array['description']);
        }
    }

    public function testOperationGeneratorWithoutOrder(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generate($route, $this->createRequestDocMock(), [TestResponse::class]);

        $this->assertEquals('Route desc', $operation->getDescription());
        $this->assertNull($operation->getSummary());
        // 200 from the controller plus 405; a bare route switches nothing else on
        $this->assertCount(2, $operation->getResponses());

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
        $this->assertCount(2, $operation->getResponses());
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

    public function testWithoutCustomResponsesOnlyMethodNotAllowedRemains(): void
    {
        $generator = new OperationGenerator();
        $route = $this->createRouteMock();

        $operation = $generator->generateFromDocumentation(
            $route,
            $this->createRequestDocMock(),
            []
        );

        $this->assertSame([405], $this->getStatusCodes($operation));
    }

    /**
     * The framework responses are not a fixed list any more. Each one is documented only where the
     * route can actually produce it, so an endpoint without a rate limit never promises a 429.
     */
    public function testFrameworkResponsesFollowWhatTheRouteSwitchesOn(): void
    {
        $generator = new OperationGenerator();

        $bare = $generator->generateFromDocumentation($this->createRouteMock(), $this->createRequestDocMock(), []);
        $this->assertSame([405], $this->getStatusCodes($bare));

        $rateLimited = $generator->generateFromDocumentation(
            $this->createRouteMock(['rateLimit' => $this->createMock(RateLimitInterface::class)]),
            $this->createRequestDocMock(),
            []
        );
        $this->assertSame([405, 429], $this->getStatusCodes($rateLimited));

        $authenticated = $generator->generateFromDocumentation(
            $this->createRouteMock(['routeAuthorization' => new RouteAuthorization('bearer')]),
            $this->createRequestDocMock(),
            []
        );
        $this->assertSame([401, 405], $this->getStatusCodes($authenticated));

        // Scopes give SecurityMiddleware something to reject with a 403.
        $authorized = $generator->generateFromDocumentation(
            $this->createRouteMock(['routeAuthorization' => new RouteAuthorization('bearer', ['read'])]),
            $this->createRequestDocMock(),
            []
        );
        $this->assertSame([401, 403, 405], $this->getStatusCodes($authorized));

        $withPathProperty = $generator->generateFromDocumentation(
            $this->createRouteMock(['pathProperties' => [new IntegerProperty('id', 'ID')]]),
            $this->createRequestDocMock(),
            []
        );
        $this->assertSame([404, 405, 422], $this->getStatusCodes($withPathProperty));
    }

    /**
     * @return array<int, int>
     */
    private function getStatusCodes(OperationObject $operation): array
    {
        $codes = [];
        foreach ($operation->getResponses() as $response) {
            $codes[] = $response->getStatusCode();
        }
        \sort($codes);

        return $codes;
    }
}
