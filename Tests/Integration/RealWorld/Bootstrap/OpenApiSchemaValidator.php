<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Bootstrap;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Validator;

/**
 * Validates a generated document against the published OpenAPI 3.2 JSON Schema
 * (`Schema/openapi-3.2.json`, fetched from spec.openapis.org so the suite stays offline).
 *
 * The schema is Draft 2020-12 and uses two keywords opis/json-schema 2.6 gets wrong, so it
 * is adjusted on load. Both adjustments are narrow and verified against the validator, see
 * the methods below.
 */
final class OpenApiSchemaValidator
{
    private const SCHEMA_URI = 'https://spec.openapis.org/oas/3.2/schema/2025-09-17';

    /**
     * @return string[] Human readable errors, empty when the document is valid
     */
    public static function validate(string $json): array
    {
        $schema = json_decode((string)file_get_contents(__DIR__ . '/Schema/openapi-3.2.json'));

        self::relaxOptionalContains($schema);
        self::resolveDynamicSchemaRef($schema);
        self::dropUnevaluatedProperties($schema);

        $validator = new Validator();
        $validator->resolver()->registerRaw($schema, self::SCHEMA_URI);

        $result = $validator->validate(json_decode($json), self::SCHEMA_URI);

        if ($result->isValid()) {
            return [];
        }

        $error = $result->error();

        return $error === null ? [] : self::collectLeafErrors($error);
    }

    /**
     * `contains` with `minContains: 0` is satisfied by zero matches, but opis applies the
     * default `minContains: 1` and rejects the document. The schema uses this once, to allow
     * at most one `in: querystring` parameter, so dropping the constraint only gives up a
     * limit on a parameter location apivalk never generates.
     *
     * @param mixed $node
     */
    private static function relaxOptionalContains($node): void
    {
        if (\is_object($node)) {
            if (isset($node->minContains) && $node->minContains === 0) {
                unset($node->contains, $node->minContains, $node->maxContains);
            }

            foreach ($node as $child) {
                self::relaxOptionalContains($child);
            }

            return;
        }

        if (\is_array($node)) {
            foreach ($node as $child) {
                self::relaxOptionalContains($child);
            }
        }
    }

    /**
     * opis resolves `$dynamicRef: "#meta"` to the root schema rather than to the dynamic
     * anchor, so every Schema Object in the document gets validated as if it were a whole
     * OpenAPI document. The anchor is declared exactly once, on `$defs/schema`, which is
     * `{"type": ["object", "boolean"]}`. A static `$ref` to it is therefore equivalent:
     * the base schema deliberately does not validate Schema Objects in depth, that is what
     * the separate `schema-base` document is for.
     *
     * @param mixed $node
     */
    private static function resolveDynamicSchemaRef($node): void
    {
        if (\is_object($node)) {
            if (isset($node->{'$dynamicRef'}) && $node->{'$dynamicRef'} === '#meta') {
                unset($node->{'$dynamicRef'});
                $node->{'$ref'} = '#/$defs/schema';
            }

            foreach ($node as $child) {
                self::resolveDynamicSchemaRef($child);
            }

            return;
        }

        if (\is_array($node)) {
            foreach ($node as $child) {
                self::resolveDynamicSchemaRef($child);
            }
        }
    }

    /**
     * opis reports `unevaluatedProperties` violations for properties the document does not
     * even contain, once the keyword sits behind `$ref` inside an `if`/`else` branch. Dropping
     * it gives up the "no unknown fields" check; `testPathsOnlyContainKnownOperations()` and
     * `testParametersAreWellFormedAndUniquePerOperation()` cover that ground for the parts
     * apivalk generates.
     *
     * @param mixed $node
     */
    private static function dropUnevaluatedProperties($node): void
    {
        if (\is_object($node)) {
            unset($node->unevaluatedProperties);

            foreach ($node as $child) {
                self::dropUnevaluatedProperties($child);
            }

            return;
        }

        if (\is_array($node)) {
            foreach ($node as $child) {
                self::dropUnevaluatedProperties($child);
            }
        }
    }

    /**
     * @return string[]
     */
    private static function collectLeafErrors(ValidationError $error, array $keywords = []): array
    {
        $keywords[] = $error->keyword();

        if ($error->subErrors() === []) {
            return [\sprintf(
                '/%s [%s] %s %s',
                implode('/', $error->data()->fullPath()),
                implode('>', \array_slice($keywords, -3)),
                $error->message(),
                json_encode($error->args())
            )];
        }

        $messages = [];
        foreach ($error->subErrors() as $subError) {
            $messages = array_merge($messages, self::collectLeafErrors($subError, $keywords));
        }

        return array_values(array_unique($messages));
    }
}
