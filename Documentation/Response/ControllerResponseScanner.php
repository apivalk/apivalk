<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Response;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

/**
 * Reads the response classes a controller's `__invoke()` constructs, so a controller does not have
 * to list them a second time.
 *
 * Only what the controller builds itself is visible here. Responses the framework produces around
 * it (401, 403, 422, 429) never appear in a controller body and are derived from the route by
 * OperationGenerator instead.
 *
 * Scanning happens while the route index is built, never per request.
 */
final class ControllerResponseScanner
{
    /**
     * @param class-string<AbstractApivalkController> $controllerClass
     *
     * @return array<int, class-string<AbstractApivalkResponse>>
     */
    public static function scan(string $controllerClass): array
    {
        $method = new \ReflectionMethod($controllerClass, '__invoke');
        $fileName = $method->getFileName();

        if ($fileName === false || !\is_readable($fileName)) {
            throw new \LogicException(\sprintf(
                'Cannot read the source of "%s::__invoke()" to determine its responses. '
                . 'Controllers defined via eval() have no source file.',
                $controllerClass
            ));
        }

        $tokens = self::tokenize((string)\file_get_contents($fileName));
        $context = self::readFileContext($tokens);
        $body = self::sliceLines($tokens, $method->getStartLine(), $method->getEndLine());

        $responseClasses = self::collectResponseClasses($body, $context);

        if ($responseClasses === []) {
            throw new \LogicException(\sprintf(
                'No response class found in "%s::__invoke()". Return the response directly '
                . '("return new MyResponse()") or assign it to a variable first, so the generated '
                . 'OpenAPI document can document it.',
                $controllerClass
            ));
        }

        return $responseClasses;
    }

    /**
     * Array tokens carry their line, string tokens like ";" do not. Giving every token a line
     * up front is what makes slicing a single method out of the stream possible.
     *
     * @return array<int, array{0: int|null, 1: string, 2: int}>
     */
    private static function tokenize(string $source): array
    {
        $tokens = [];
        $line = 1;

        foreach (\token_get_all($source) as $token) {
            if (\is_array($token)) {
                $tokens[] = [$token[0], $token[1], $token[2]];
                $line = $token[2] + \substr_count($token[1], "\n");

                continue;
            }

            $tokens[] = [null, $token, $line];
        }

        return $tokens;
    }

    /**
     * @param array<int, array{0: int|null, 1: string, 2: int}> $tokens
     *
     * @return array{namespace: string, uses: array<string, string>}
     */
    private static function readFileContext(array $tokens): array
    {
        $namespace = '';
        $uses = [];
        $depth = 0;

        for ($i = 0, $count = \count($tokens); $i < $count; $i++) {
            [$id, $text] = $tokens[$i];

            if ($text === '{' || $id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES) {
                $depth++;

                continue;
            }

            if ($text === '}') {
                $depth--;

                continue;
            }

            // Below the top level a "use" is a trait import or a closure binding, never an import.
            if ($depth > 0) {
                continue;
            }

            if ($id === T_NAMESPACE) {
                $namespace = \ltrim(self::readName($tokens, $i), '\\');

                continue;
            }

            if ($id === T_USE) {
                self::readUseStatement($tokens, $i, $uses);
            }
        }

        return ['namespace' => $namespace, 'uses' => $uses];
    }

    /**
     * Handles `use A\B;`, `use A\B as C;` and the group form `use A\{B, C as D};`.
     *
     * @param array<int, array{0: int|null, 1: string, 2: int}> $tokens
     * @param array<string, string>                             $uses
     */
    private static function readUseStatement(array $tokens, int &$i, array &$uses): void
    {
        $prefix = self::readName($tokens, $i);

        if ($prefix === '') {
            return;
        }

        $next = self::nextMeaningful($tokens, $i);

        if ($next !== null && $next[1] === '{') {
            $i = $next[3];
            $prefix = \rtrim($prefix, '\\');

            while ($i < \count($tokens)) {
                $name = self::readName($tokens, $i);

                if ($name !== '') {
                    self::addUse($uses, $prefix . '\\' . $name, self::readAlias($tokens, $i));
                }

                $next = self::nextMeaningful($tokens, $i);

                if ($next === null || $next[1] === '}' || $next[1] === ';') {
                    return;
                }

                $i = $next[3];
            }

            return;
        }

        self::addUse($uses, $prefix, self::readAlias($tokens, $i));
    }

    /**
     * @param array<int, array{0: int|null, 1: string, 2: int}> $tokens
     */
    private static function readAlias(array $tokens, int &$i): ?string
    {
        $next = self::nextMeaningful($tokens, $i);

        if ($next === null || $next[0] !== T_AS) {
            return null;
        }

        $i = $next[3];

        return self::readName($tokens, $i);
    }

    /**
     * @param array<string, string> $uses
     */
    private static function addUse(array &$uses, string $target, ?string $alias): void
    {
        $target = \ltrim($target, '\\');

        if ($alias === null || $alias === '') {
            $segments = \explode('\\', $target);
            $alias = \end($segments);
        }

        $uses[$alias] = $target;
    }

    /**
     * Reads the (possibly qualified) name that follows position $i and leaves $i on its last token.
     *
     * @param array<int, array{0: int|null, 1: string, 2: int}> $tokens
     */
    private static function readName(array $tokens, int &$i): string
    {
        $name = '';
        $count = \count($tokens);

        for ($i++; $i < $count; $i++) {
            [$id, $text] = $tokens[$i];

            if ($id === T_WHITESPACE || $id === T_COMMENT || $id === T_DOC_COMMENT) {
                if ($name !== '') {
                    break;
                }

                continue;
            }

            if (!self::isNamePart($id)) {
                break;
            }

            $name .= $text;
        }

        $i--;

        return $name;
    }

    /**
     * PHP 8 folds a qualified name into one token, PHP 7.4 emits it as a T_STRING/T_NS_SEPARATOR
     * sequence. Both have to be understood, the library supports 7.4 and 8.
     */
    private static function isNamePart(?int $id): bool
    {
        static $ids = null;

        if ($ids === null) {
            $ids = [T_STRING => true, T_NS_SEPARATOR => true];

            foreach (['T_NAME_QUALIFIED', 'T_NAME_FULLY_QUALIFIED', 'T_NAME_RELATIVE'] as $constant) {
                if (\defined($constant)) {
                    $ids[\constant($constant)] = true;
                }
            }
        }

        return $id !== null && isset($ids[$id]);
    }

    /**
     * @param array<int, array{0: int|null, 1: string, 2: int}> $tokens
     *
     * @return array{0: int|null, 1: string, 2: int, 3: int}|null
     */
    private static function nextMeaningful(array $tokens, int $i): ?array
    {
        for ($j = $i + 1, $count = \count($tokens); $j < $count; $j++) {
            $id = $tokens[$j][0];

            if ($id === T_WHITESPACE || $id === T_COMMENT || $id === T_DOC_COMMENT) {
                continue;
            }

            return [$id, $tokens[$j][1], $tokens[$j][2], $j];
        }

        return null;
    }

    /**
     * @param array<int, array{0: int|null, 1: string, 2: int}> $tokens
     *
     * @return array<int, array{0: int|null, 1: string, 2: int}>
     */
    private static function sliceLines(array $tokens, int $startLine, int $endLine): array
    {
        $slice = [];

        foreach ($tokens as $token) {
            if ($token[2] >= $startLine && $token[2] <= $endLine) {
                $slice[] = $token;
            }
        }

        return $slice;
    }

    /**
     * @param array<int, array{0: int|null, 1: string, 2: int}> $body
     * @param array{namespace: string, uses: array<string, string>} $context
     *
     * @return array<int, class-string<AbstractApivalkResponse>>
     */
    private static function collectResponseClasses(array $body, array $context): array
    {
        $assignedByVariable = [];
        $returnedClasses = [];
        $returnedVariables = [];

        for ($i = 0, $count = \count($body); $i < $count; $i++) {
            [$id, $text] = $body[$i];

            if ($id === T_VARIABLE) {
                $assigned = self::readAssignedClass($body, $i, $context);

                if ($assigned !== null) {
                    $assignedByVariable[$text] = $assigned;
                }

                continue;
            }

            if ($id !== T_RETURN) {
                continue;
            }

            // The whole expression is read, not just the token after `return`, so that a ternary
            // or a null coalesce contributes every branch it can produce.
            $end = self::findStatementEnd($body, $i);
            $meaningfulCount = 0;
            $onlyToken = null;

            for ($j = $i + 1; $j < $end; $j++) {
                $tokenId = $body[$j][0];

                if ($tokenId === T_WHITESPACE || $tokenId === T_COMMENT || $tokenId === T_DOC_COMMENT) {
                    continue;
                }

                $meaningfulCount++;
                $onlyToken = $body[$j];

                if ($tokenId !== T_NEW) {
                    continue;
                }

                $class = self::readInstantiatedClass($body, $j, $context);

                if ($class !== null) {
                    $returnedClasses[$class] = true;
                }
            }

            // Only a bare `return $response;` is traced back. Anything else would pick up
            // variables that are merely arguments to whatever actually builds the response.
            if ($meaningfulCount === 1 && $onlyToken !== null && $onlyToken[0] === T_VARIABLE) {
                $returnedVariables[$onlyToken[1]] = true;
            }

            $i = $end;
        }

        // `$response = new X(); $response->setPagination(...); return $response;` is the idiom every
        // paginated list controller is forced into, so a returned variable has to be followed back.
        foreach (\array_keys($returnedVariables) as $variable) {
            if (isset($assignedByVariable[$variable])) {
                $returnedClasses[$assignedByVariable[$variable]] = true;
            }
        }

        return \array_keys($returnedClasses);
    }

    /**
     * Index of the semicolon closing the statement that starts at $i, ignoring semicolons nested
     * in parentheses, brackets or an anonymous class body.
     *
     * @param array<int, array{0: int|null, 1: string, 2: int}> $body
     */
    private static function findStatementEnd(array $body, int $i): int
    {
        $depth = 0;
        $count = \count($body);

        for ($j = $i + 1; $j < $count; $j++) {
            $text = $body[$j][1];

            if ($text === '(' || $text === '[' || $text === '{') {
                $depth++;

                continue;
            }

            if ($text === ')' || $text === ']' || $text === '}') {
                $depth--;

                continue;
            }

            if ($text === ';' && $depth === 0) {
                return $j;
            }
        }

        return $count;
    }

    /**
     * @param array<int, array{0: int|null, 1: string, 2: int}> $body
     * @param array{namespace: string, uses: array<string, string>} $context
     *
     * @return class-string<AbstractApivalkResponse>|null
     */
    private static function readAssignedClass(array $body, int $i, array $context): ?string
    {
        $assignment = self::nextMeaningful($body, $i);

        if ($assignment === null || $assignment[1] !== '=') {
            return null;
        }

        $new = self::nextMeaningful($body, $assignment[3]);

        if ($new === null || $new[0] !== T_NEW) {
            return null;
        }

        return self::readInstantiatedClass($body, $new[3], $context);
    }

    /**
     * @param array<int, array{0: int|null, 1: string, 2: int}> $body
     * @param array{namespace: string, uses: array<string, string>} $context
     *
     * @return class-string<AbstractApivalkResponse>|null
     */
    private static function readInstantiatedClass(array $body, int $newIndex, array $context): ?string
    {
        $next = self::nextMeaningful($body, $newIndex);

        // `new class extends AbstractApivalkResponse {}` has no name to put into a document.
        if ($next === null || $next[0] === T_CLASS || !self::isNamePart($next[0])) {
            return null;
        }

        $i = $newIndex;
        $name = self::readName($body, $i);

        if ($name === '') {
            return null;
        }

        $className = self::resolveClassName($name, $context);

        if (!\is_subclass_of($className, AbstractApivalkResponse::class)) {
            return null;
        }

        return $className;
    }

    /**
     * @param array{namespace: string, uses: array<string, string>} $context
     */
    private static function resolveClassName(string $name, array $context): string
    {
        if (\strpos($name, '\\') === 0) {
            return \ltrim($name, '\\');
        }

        $segments = \explode('\\', $name);
        $alias = $segments[0];

        if (isset($context['uses'][$alias])) {
            $segments[0] = $context['uses'][$alias];

            return \implode('\\', $segments);
        }

        if ($context['namespace'] !== '') {
            $candidate = $context['namespace'] . '\\' . $name;

            if (\class_exists($candidate)) {
                return $candidate;
            }
        }

        return $name;
    }
}
