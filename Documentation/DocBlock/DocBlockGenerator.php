<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Util\ClassLocator;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;

class DocBlockGenerator
{
    public function run(string $apiDirectory, string $namespace): void
    {
        if (!is_dir($apiDirectory)) {
            throw new \RuntimeException(\sprintf('Invalid API directory: %s', $apiDirectory));
        }

        $classLocator = new ClassLocator($apiDirectory, $namespace);
        $generator = new DocBlockRequestGenerator();

        foreach ($classLocator->find() as $class) {
            $className = $class['className'];
            $filePath = $class['path'];

            if (!is_subclass_of($className, ApivalkRequestInterface::class)) {
                continue;
            }

            try {
                /** @var AbstractApivalkRequest $request */
                $request = new $className();

                $docBlockRequest = $generator->generate($request);

                $this->rewriteRequestFileWithDocblocks($filePath, $docBlockRequest);

                echo "✔ DocBlocks & Shapes generated for {$className}\n";
            } catch (\Throwable $e) {
                echo "⚠ Error in class {$className}: {$e->getMessage()}\n";
            }
        }
    }

    private function rewriteRequestFileWithDocblocks(
        string $filePath,
        DocBlockRequest $docBlockRequest
    ): void {
        $content = file_get_contents($filePath);
        if (!$content) {
            throw new \RuntimeException("Unable to read file: $filePath");
        }

        if (!preg_match('/^namespace\s+([^;]+);/m', $content, $namespaceMatch)) {
            throw new \RuntimeException("Unable to detect namespace in $filePath");
        }

        $requestNamespace = trim($namespaceMatch[1]);
        $shapeNamespace = $docBlockRequest->getShapeNamespace($requestNamespace);

        if (!preg_match('/^\s*class\s+([A-Za-z0-9_]+)/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
            throw new \RuntimeException("Could not find class declaration in $filePath");
        }

        $classOffset = (int)$matches[0][1];

        $beforeClass = substr($content, 0, $classOffset);
        $afterClass = substr($content, $classOffset);

        $beforeClass = preg_replace('/\/\*\*(?:[^*]|\*(?!\/))*\*\//s', '', $beforeClass);
        $beforeClass = rtrim($beforeClass) . "\n\n";

        $requestDoc = $docBlockRequest->getRequestDocBlockOnly($shapeNamespace);

        $newContent = $beforeClass . $requestDoc . "\n" . ltrim($afterClass);

        if (file_put_contents($filePath, $newContent) === false) {
            throw new \RuntimeException(\sprintf('Failed to write file: %s', $filePath));
        }

        $shapeDir = dirname($filePath) . '/Shape';
        if (!is_dir($shapeDir) && !mkdir($shapeDir) && !is_dir($shapeDir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $shapeDir));
        }

        $filenames = $docBlockRequest->getShapeFilenames(dirname($filePath));

        if (file_put_contents(
                $filenames['path'],
                $docBlockRequest->getPathShape()->toString($shapeNamespace)
            ) === false) {
            throw new \RuntimeException('Failed to write path shape file');
        }

        if (file_put_contents(
                $filenames['query'],
                $docBlockRequest->getQueryShape()->toString($shapeNamespace)
            ) ===
            false) {
            throw new \RuntimeException('Failed to write query shape file');
        }

        if (file_put_contents(
                $filenames['body'],
                $docBlockRequest->getBodyShape()->toString($shapeNamespace)
            ) ===
            false) {
            throw new \RuntimeException('Failed to write body shape file');
        }
    }
}
