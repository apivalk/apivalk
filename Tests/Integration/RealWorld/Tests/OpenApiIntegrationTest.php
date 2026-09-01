<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Tests;

use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;
use apivalk\apivalk\Documentation\OpenAPI\OpenAPIGenerator;
use apivalk\apivalk\Http\Method\MethodInterface;
use PHPUnit\Framework\TestCase;
use Tests\Integration\RealWorld\Bootstrap\ApiFactory;
use Tests\Integration\RealWorld\Bootstrap\InMemoryCache;
use Tests\Integration\RealWorld\Bootstrap\OpenApiSchemaValidator;

/**
 * Generates one OpenAPI document from every controller, request and response in the
 * integration fixtures and asserts it is structurally valid. Unit tests cover the
 * generators in isolation; this catches a change that only breaks once real routes with
 * filters, sorting, pagination, path parameters, uploads and auth are combined.
 */
class OpenApiIntegrationTest extends TestCase
{
    /** @var array<string, mixed> */
    private static array $document;

    private static string $json;

    /** @var string[] */
    private const OPERATION_KEYS = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace', 'query'];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_SERVER['REQUEST_METHOD'] = MethodInterface::METHOD_GET;
        $_SERVER['REQUEST_URI'] = '/';

        $generator = new OpenAPIGenerator(
            ApiFactory::create(new InMemoryCache()),
            new InfoObject('Apivalk integration fixtures', '1.0.0')
        );

        self::$json = $generator->generate(OpenAPIGenerator::FORMAT_JSON);
        $document = json_decode(self::$json, true);

        self::assertIsArray($document, 'Generated OpenAPI document is not valid JSON.');

        self::$document = $document;
    }

    /**
     * The assertions below describe what apivalk promises on top of the specification. This
     * one checks the specification itself, against the schema published by the OpenAPI
     * Initiative, so a generator change cannot quietly produce a document that only looks
     * right to our own expectations.
     */
    public function testDocumentValidatesAgainstTheOfficialOpenApi32Schema(): void
    {
        $errors = OpenApiSchemaValidator::validate(self::$json);

        $this->assertSame(
            [],
            $errors,
            "Generated document does not satisfy the OpenAPI 3.2 schema:\n" . implode("\n", $errors)
        );
    }

    public function testTheSchemaValidatorRejectsABrokenDocument(): void
    {
        $errors = OpenApiSchemaValidator::validate('{"info":{"title":"t","version":"1"},"paths":{}}');

        $this->assertNotEmpty($errors, 'A document without "openapi" must not validate.');
    }

    /**
     * PHP serialises an empty array as `[]`, so a map that happens to be empty turns into a
     * JSON array and stops being a valid Map or Schema Object. The published base schema does
     * not catch this, it types Schema Objects as `["object", "boolean"]` and leaves the dialect
     * to `schema-base`, so external validators report it while ours would not.
     */
    public function testMapsAreNeverSerialisedAsArrays(): void
    {
        $objectKeys = [
            'properties', 'content', 'responses', 'paths', 'schemas', 'headers',
            'examples', 'components', 'info', 'encoding', 'securitySchemes', 'scopes',
        ];

        $offenders = [];
        $walk = static function ($node, string $path) use (&$walk, $objectKeys, &$offenders): void {
            if (\is_object($node)) {
                foreach (get_object_vars($node) as $key => $value) {
                    if (\in_array($key, $objectKeys, true) && \is_array($value)) {
                        $offenders[] = $path . '/' . $key;
                    }

                    $walk($value, $path . '/' . $key);
                }

                return;
            }

            if (\is_array($node)) {
                foreach ($node as $index => $value) {
                    $walk($value, $path . '/' . $index);
                }
            }
        };

        $walk(json_decode(self::$json), '');

        $this->assertSame([], $offenders, 'These map-typed keys serialised as JSON arrays: ' . implode(', ', $offenders));
    }

    public function testDocumentDeclaresTheSupportedSpecVersion(): void
    {
        $this->assertSame('3.2.0', self::$document['openapi']);
    }

    public function testInfoObjectIsComplete(): void
    {
        $this->assertArrayHasKey('info', self::$document);
        $this->assertNotEmpty(self::$document['info']['title']);
        $this->assertNotEmpty(self::$document['info']['version']);
    }

    public function testEveryFixtureRouteIsDocumented(): void
    {
        $paths = self::$document['paths'];

        $this->assertGreaterThanOrEqual(9, \count($paths), 'Expected the fixture routes to be documented.');
        $this->assertArrayHasKey('/v1/api/customers', $paths);
        $this->assertArrayHasKey('/v1/api/contracts', $paths);
    }

    public function testPathsOnlyContainKnownOperations(): void
    {
        foreach (self::$document['paths'] as $path => $pathItem) {
            foreach (array_keys($pathItem) as $key) {
                if (\in_array($key, ['summary', 'description', 'parameters', 'servers'], true)) {
                    continue;
                }

                $this->assertContains(
                    $key,
                    self::OPERATION_KEYS,
                    \sprintf('Path "%s" declares unknown operation "%s".', $path, $key)
                );
            }
        }
    }

    public function testEveryOperationHasAnIdSummaryAndResponses(): void
    {
        foreach ($this->operations() as [$path, $method, $operation]) {
            $context = \sprintf('%s %s', strtoupper($method), $path);

            $this->assertArrayHasKey('operationId', $operation, $context . ' has no operationId.');
            $this->assertNotEmpty($operation['responses'], $context . ' declares no response.');

            foreach ($operation['responses'] as $status => $response) {
                $this->assertMatchesRegularExpression(
                    '/^[1-5]\d{2}$/',
                    (string)$status,
                    $context . ' has a non-numeric response status.'
                );
                $this->assertArrayHasKey('description', $response, $context . ' response ' . $status . ' has no description.');
            }
        }
    }

    public function testOperationIdsAreUnique(): void
    {
        $ids = [];

        foreach ($this->operations() as [$path, $method, $operation]) {
            $id = $operation['operationId'];
            $this->assertArrayNotHasKey(
                $id,
                $ids,
                \sprintf('operationId "%s" is used by %s and %s %s.', $id, $ids[$id] ?? '', strtoupper($method), $path)
            );
            $ids[$id] = strtoupper($method) . ' ' . $path;
        }

        $this->assertNotEmpty($ids);
    }

    public function testParametersAreWellFormedAndUniquePerOperation(): void
    {
        foreach ($this->operations() as [$path, $method, $operation]) {
            $seen = [];

            foreach ($operation['parameters'] ?? [] as $parameter) {
                $context = \sprintf('%s %s parameter "%s"', strtoupper($method), $path, $parameter['name'] ?? '?');

                $this->assertArrayHasKey('name', $parameter, $context);
                $this->assertContains($parameter['in'], ['query', 'header', 'path', 'cookie'], $context);
                $this->assertTrue(
                    isset($parameter['schema']) || isset($parameter['content']),
                    $context . ' has neither schema nor content.'
                );

                if ($parameter['in'] === 'path') {
                    $this->assertTrue($parameter['required'] ?? false, $context . ' is a path parameter but not required.');
                }

                $key = $parameter['in'] . ':' . $parameter['name'];
                $this->assertArrayNotHasKey($key, $seen, $context . ' is declared twice.');
                $seen[$key] = true;
            }
        }
    }

    public function testPathParametersMatchThePathTemplate(): void
    {
        foreach ($this->operations() as [$path, $method, $operation]) {
            preg_match_all('/\{([^}]+)\}/', $path, $matches);
            $declared = [];

            foreach ($operation['parameters'] ?? [] as $parameter) {
                if ($parameter['in'] === 'path') {
                    $declared[] = $parameter['name'];
                }
            }

            sort($matches[1]);
            sort($declared);

            $this->assertSame(
                $matches[1],
                $declared,
                \sprintf('%s %s declares path parameters that do not match its template.', strtoupper($method), $path)
            );
        }
    }

    public function testFilterParametersAreDeepObjectsWithOperatorProperties(): void
    {
        $checked = 0;

        foreach ($this->operations() as [$path, $method, $operation]) {
            foreach ($operation['parameters'] ?? [] as $parameter) {
                if (($parameter['style'] ?? null) !== 'deepObject') {
                    continue;
                }

                $context = \sprintf('%s %s filter "%s"', strtoupper($method), $path, $parameter['name']);

                $this->assertTrue($parameter['explode'] ?? false, $context . ' is deepObject but not exploded.');
                $this->assertSame('object', $parameter['schema']['type'], $context);
                $this->assertFalse($parameter['schema']['additionalProperties'], $context . ' allows unknown operators.');
                $this->assertNotEmpty($parameter['schema']['properties'], $context . ' declares no operator.');

                foreach ($parameter['schema']['properties'] as $operator => $schema) {
                    $this->assertContains(
                        $operator,
                        ['eq', 'neq', 'in', 'gt', 'gte', 'lt', 'lte', 'like', 'contains', 'null'],
                        $context . ' declares unknown operator "' . $operator . '".'
                    );
                    $this->assertArrayHasKey('type', $schema, $context . ' operator "' . $operator . '" has no type.');
                }

                ++$checked;
            }
        }

        $this->assertGreaterThan(0, $checked, 'Expected the fixtures to document at least one filter.');
    }

    public function testQueryOperationsMirrorTheirFiltersAsARequestBody(): void
    {
        $queryOperations = 0;

        foreach (self::$document['paths'] as $path => $pathItem) {
            if (!isset($pathItem['query'])) {
                continue;
            }

            $schema = $pathItem['query']['requestBody']['content']['application/json']['schema'];

            $this->assertSame('object', $schema['type'], $path);
            $this->assertFalse($schema['additionalProperties'], $path . ' query body allows unknown fields.');

            $bodyFields = array_keys($schema['properties']);
            $queryFields = [];

            foreach ($pathItem['get']['parameters'] ?? [] as $parameter) {
                if (($parameter['style'] ?? null) === 'deepObject') {
                    $queryFields[] = $parameter['name'];
                }
            }

            sort($bodyFields);
            sort($queryFields);

            $this->assertSame(
                $queryFields,
                $bodyFields,
                $path . ' documents different filters for GET and QUERY.'
            );

            ++$queryOperations;
        }

        $this->assertGreaterThan(0, $queryOperations, 'Expected at least one route with enableQuery().');
    }

    public function testRequestBodiesAndResponsesCarryAMediaType(): void
    {
        foreach ($this->operations() as [$path, $method, $operation]) {
            $context = \sprintf('%s %s', strtoupper($method), $path);

            if (isset($operation['requestBody'])) {
                $this->assertNotEmpty($operation['requestBody']['content'], $context . ' request body has no content.');
            }

            foreach ($operation['responses'] as $status => $response) {
                foreach ($response['content'] ?? [] as $mediaType => $definition) {
                    $this->assertMatchesRegularExpression(
                        '#^[a-z]+/[a-z0-9.+*-]+$#',
                        $mediaType,
                        $context . ' response ' . $status . ' has an invalid media type.'
                    );
                    $this->assertArrayHasKey('schema', $definition, $context . ' response ' . $status . ' has no schema.');
                }
            }
        }
    }

    public function testExcludedRoutesAreNotDocumented(): void
    {
        $json = json_encode(self::$document);

        $this->assertIsString($json);
        $this->assertStringNotContainsString('excludeFromDocumentation', $json);
    }

    /**
     * @return \Generator<int, array{0: string, 1: string, 2: array<string, mixed>}>
     */
    private function operations(): \Generator
    {
        foreach (self::$document['paths'] as $path => $pathItem) {
            foreach ($pathItem as $method => $operation) {
                if (\in_array($method, self::OPERATION_KEYS, true)) {
                    yield [$path, $method, $operation];
                }
            }
        }
    }
}
