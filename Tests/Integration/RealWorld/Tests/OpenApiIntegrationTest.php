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

    /**
     * Declared filter field names per url and lowercased http method, taken from the routes
     * themselves. A filter parameter is not recognisable by shape any more, a single-operator
     * one is a plain query parameter, so the assertions below need the route as the source of
     * truth rather than the generated document.
     *
     * @var array<string, array<string, string[]>>
     */
    private static array $declaredFilters = [];

    /** @var string[] */
    private const OPERATION_KEYS = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace', 'query'];

    /** @var string[] */
    private const OPERATORS = ['eq', 'neq', 'in', 'gt', 'gte', 'lt', 'lte', 'like', 'contains', 'null'];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_SERVER['REQUEST_METHOD'] = MethodInterface::METHOD_GET;
        $_SERVER['REQUEST_URI'] = '/';

        $apivalk = ApiFactory::create(new InMemoryCache());

        foreach ($apivalk->getRouter()->getRoutes() as $entry) {
            $route = $entry['route'];
            $fields = [];

            foreach ($route->getFilters() as $filter) {
                $fields[] = $filter->getField();
            }

            sort($fields);

            self::$declaredFilters[$route->getUrl()][strtolower($route->getMethod()->getName())] = $fields;
        }

        $generator = new OpenAPIGenerator(
            $apivalk,
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

    /**
     * Every declared filter must reach the document, in the form its operator count dictates:
     * flat with the operator named in the description when it declares one, a deepObject of
     * operators when it declares several.
     */
    public function testEveryDeclaredFilterIsDocumentedInTheFormItsOperatorCountDictates(): void
    {
        $flat = 0;
        $deepObjects = 0;

        foreach ($this->operations() as [$path, $method, $operation]) {
            $declared = self::$declaredFilters[$path][$method] ?? [];

            if ($declared === [] || $method === 'query') {
                continue;
            }

            $parameters = [];

            foreach ($operation['parameters'] ?? [] as $parameter) {
                if ($parameter['in'] === 'query') {
                    $parameters[$parameter['name']] = $parameter;
                }
            }

            foreach ($declared as $field) {
                $context = \sprintf('%s %s filter "%s"', strtoupper($method), $path, $field);

                $this->assertArrayHasKey($field, $parameters, $context . ' is not documented.');

                $parameter = $parameters[$field];

                // The serializer drops falsy keys, so an absent `required` is the documented false.
                $this->assertFalse($parameter['required'] ?? false, $context . ' is documented as required.');

                if (($parameter['style'] ?? null) === 'deepObject') {
                    $this->assertTrue($parameter['explode'] ?? false, $context . ' is deepObject but not exploded.');
                    $this->assertSame('object', $parameter['schema']['type'], $context);
                    $this->assertFalse(
                        $parameter['schema']['additionalProperties'],
                        $context . ' allows unknown operators.'
                    );
                    $this->assertGreaterThan(
                        1,
                        \count($parameter['schema']['properties']),
                        $context . ' is a deepObject for a single operator.'
                    );

                    foreach ($parameter['schema']['properties'] as $operator => $schema) {
                        $this->assertContains(
                            $operator,
                            self::OPERATORS,
                            $context . ' declares unknown operator "' . $operator . '".'
                        );
                        $this->assertArrayHasKey(
                            'type',
                            $schema,
                            $context . ' operator "' . $operator . '" has no type.'
                        );
                    }

                    ++$deepObjects;

                    continue;
                }

                if (($parameter['style'] ?? null) === 'form') {
                    // The one flat filter that needs a style: a list serialises as `?f=a,b`
                    // only with explode turned off, form defaults to exploding.
                    $this->assertFalse($parameter['explode'], $context . ' is a form list but explodes.');
                    $this->assertSame('array', $parameter['schema']['type'], $context);
                    $this->assertArrayHasKey('items', $parameter['schema'], $context . ' is an array without items.');
                } else {
                    $this->assertArrayNotHasKey('explode', $parameter, $context . ' is flat but sets explode.');
                    $this->assertNotSame('array', $parameter['schema']['type'], $context . ' is an array without a style.');
                }

                $this->assertNotSame('object', $parameter['schema']['type'], $context . ' is flat but an object.');
                $this->assertArrayNotHasKey(
                    'properties',
                    $parameter['schema'],
                    $context . ' is flat but carries operator properties.'
                );
                $this->assertMatchesRegularExpression(
                    '/ filtering on `' . preg_quote($field, '/') . '`\./',
                    $parameter['description'],
                    $context . ' does not name its operator in the description.'
                );

                ++$flat;
            }
        }

        $this->assertGreaterThan(0, $flat, 'Expected the fixtures to document at least one flat filter.');
        $this->assertGreaterThan(0, $deepObjects, 'Expected the fixtures to document at least one deepObject filter.');
    }

    /**
     * A filter parameter describes a condition the client opts into. A schema default would
     * claim the server applies that value when the parameter is omitted, which it never does.
     */
    public function testFilterParametersNeverCarryASchemaDefault(): void
    {
        foreach ($this->operations() as [$path, $method, $operation]) {
            foreach (self::$declaredFilters[$path][$method] ?? [] as $field) {
                foreach ($operation['parameters'] ?? [] as $parameter) {
                    if ($parameter['name'] !== $field || $parameter['in'] !== 'query') {
                        continue;
                    }

                    $context = \sprintf('%s %s filter "%s"', strtoupper($method), $path, $field);

                    $this->assertArrayNotHasKey('default', $parameter['schema'], $context . ' carries a default.');

                    foreach ($parameter['schema']['properties'] ?? [] as $operator => $schema) {
                        $this->assertArrayNotHasKey(
                            'default',
                            $schema,
                            $context . ' operator "' . $operator . '" carries a default.'
                        );
                    }
                }
            }
        }
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

            // A single-operator filter is documented flat, so a filter parameter is no longer
            // distinguishable from order_by or a pagination parameter by shape. The routes are
            // the source of truth for which query parameters are filters.
            $declared = self::$declaredFilters[$path]['get'] ?? [];

            $this->assertNotEmpty($declared, $path . ' has enableQuery() but declares no filter.');

            $bodyFields = array_keys($schema['properties']);
            sort($bodyFields);

            $this->assertSame($declared, $bodyFields, $path . ' query body does not mirror the declared filters.');

            $getSchemas = [];

            foreach ($pathItem['get']['parameters'] ?? [] as $parameter) {
                if ($parameter['in'] === 'query') {
                    $getSchemas[$parameter['name']] = $parameter['schema'];
                }
            }

            foreach ($declared as $field) {
                $context = \sprintf('%s filter "%s"', $path, $field);

                $this->assertArrayHasKey($field, $getSchemas, $context . ' is documented on QUERY but not on GET.');
                $this->assertSame(
                    $schema['properties'][$field],
                    $getSchemas[$field],
                    $context . ' differs between GET and QUERY.'
                );
            }

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
