<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Controller\Resource\AbstractListResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractResourceController;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Util\ClassLocator;

class DocBlockGenerator
{
    public function run(string $apiDirectory, string $namespace): void
    {
        if (!is_dir($apiDirectory)) {
            throw new \RuntimeException(\sprintf('Invalid API directory: %s', $apiDirectory));
        }

        $classLocator = new ClassLocator($apiDirectory, $namespace);
        $generator = new DocBlockRequestGenerator();
        $resourceGenerator = new DocBlockResourceGenerator();
        $resourceRequestGenerator = new DocBlockResourceRequestGenerator();
        $processedResources = [];

        foreach ($classLocator->find() as $class) {
            $className = $class['className'];

            if (!is_subclass_of($className, AbstractApivalkController::class)) {
                continue;
            }

            try {
                /** @var class-string<AbstractApivalkController> $className */

                if (is_subclass_of($className, AbstractResourceController::class)) {
                    /** @var class-string<AbstractResourceController<AbstractResource>> $className */
                    $this->processResourceController($className, $processedResources, $resourceGenerator, $resourceRequestGenerator);
                    continue;
                }

                $this->processRegularController($className, $generator);
            } catch (\Throwable $e) {
                echo "⚠ Error in controller {$className}: {$e->getMessage()}\n";
            }
        }
    }

    /**
     * @param class-string<AbstractResourceController<AbstractResource>> $className
     * @param array<string, bool>                                        $processedResources
     */
    private function processResourceController(
        string $className,
        array &$processedResources,
        DocBlockResourceGenerator $resourceGenerator,
        DocBlockResourceRequestGenerator $resourceRequestGenerator
    ): void {
        $resourceClass = $className::getResourceClass();

        if (!isset($processedResources[$resourceClass])) {
            $processedResources[$resourceClass] = true;

            /** @var AbstractResource $resource */
            $resource = new $resourceClass();
            $docBlockResource = $resourceGenerator->generate($resource);

            $resourceReflection = new \ReflectionClass($resourceClass);
            $resourceFilePath = $resourceReflection->getFileName();

            if ($resourceFilePath) {
                $this->rewriteResourceFileWithDocblocks($resourceFilePath, $docBlockResource);
                echo "✔ Resource @property docblocks generated for {$resourceClass}\n";
            }
        }

        if (!is_subclass_of($className, AbstractListResourceController::class)) {
            return;
        }

        /** @var class-string<AbstractListResourceController<AbstractResource>> $className */
        $controllerReflection = new \ReflectionClass($className);
        $controllerFilePath = $controllerReflection->getFileName();

        if (!$controllerFilePath) {
            return;
        }

        $controllerNamespace = $controllerReflection->getNamespaceName();
        $resourceBaseNamespace = substr($controllerNamespace, 0, strrpos($controllerNamespace, '\\'));
        $resourceBaseDir = dirname(dirname($controllerFilePath));

        $docBlockResourceRequest = $resourceRequestGenerator->generate($className);

        $resource = $className::getEmptyResource();
        $requestClassName = \ucfirst($resource->getName()) . 'ListRequest';
        $requestNamespace = $resourceBaseNamespace . '\\Request';
        $requestFilePath = $resourceBaseDir . '/Request/' . $requestClassName . '.php';

        $this->rewriteResourceRequestFileWithDocblocks(
            $requestFilePath,
            $requestNamespace,
            $requestClassName,
            $docBlockResourceRequest
        );

        echo "✔ Resource request shapes generated for {$requestClassName} (from {$className})\n";
    }

    /**
     * @param class-string<AbstractApivalkController> $className
     */
    private function processRegularController(string $className, DocBlockRequestGenerator $generator): void
    {
        $requestClass = $className::getRequestClass();
        $route = $className::getRoute();

        if (!is_subclass_of($requestClass, ApivalkRequestInterface::class)) {
            return;
        }

        $reflection = new \ReflectionClass($requestClass);
        $filePath = $reflection->getFileName();

        if (!$filePath) {
            return;
        }

        /** @var AbstractApivalkRequest $request */
        $request = new $requestClass();

        if ($request instanceof ResourceRequest) {
            return;
        }

        $docBlockRequest = $generator->generate($request, $route);

        $this->rewriteRequestFileWithDocblocks($filePath, $docBlockRequest);

        echo "✔ DocBlocks & Shapes generated for {$requestClass} (from {$className})\n";
    }

    private function rewriteResourceFileWithDocblocks(
        string $filePath,
        DocBlockResource $docBlockResource
    ): void {
        $this->rewriteClassDocblock(
            $filePath,
            $docBlockResource->getResourceDocBlock(),
            true
        );
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

        $shapeNamespace = $docBlockRequest->getShapeNamespace(trim($namespaceMatch[1]));

        $this->rewriteClassDocblock($filePath, $docBlockRequest->getRequestDocBlockOnly($shapeNamespace));

        $shapeDir = dirname($filePath) . '/Shape';
        if (!is_dir($shapeDir) && !mkdir($shapeDir) && !is_dir($shapeDir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $shapeDir));
        }

        $filenames = $docBlockRequest->getShapeFilenames(dirname($filePath));

        foreach ([
            'path'      => [$docBlockRequest->getPathShape(), $shapeNamespace],
            'query'     => [$docBlockRequest->getQueryShape(), $shapeNamespace],
            'body'      => [$docBlockRequest->getBodyShape(), $shapeNamespace],
            'sorting'   => [$docBlockRequest->getSortingShape(), $shapeNamespace],
            'filtering' => [$docBlockRequest->getFilteringShape(), $shapeNamespace],
        ] as $key => [$shape, $ns]) {
            if (file_put_contents($filenames[$key], $shape->toString($ns)) === false) {
                throw new \RuntimeException(\sprintf('Failed to write %s shape file', $key));
            }
        }
    }

    private function rewriteResourceRequestFileWithDocblocks(
        string $filePath,
        string $requestNamespace,
        string $requestClassName,
        DocBlockResourceRequest $docBlockResourceRequest
    ): void {
        $requestDir = dirname($filePath);

        if (!is_dir($requestDir) && !mkdir($requestDir, 0755, true) && !is_dir($requestDir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $requestDir));
        }

        $shapeNamespace = $docBlockResourceRequest->getShapeNamespace($requestNamespace);
        $docBlock = $docBlockResourceRequest->getDocBlockOnly($shapeNamespace);

        if (!file_exists($filePath)) {
            $baseClass = '\\' . $docBlockResourceRequest->getBaseRequestClass();

            $stub = <<<PHP
<?php

declare(strict_types=1);

namespace {$requestNamespace};

{$docBlock}
class {$requestClassName} extends {$baseClass}
{
}
PHP;

            if (file_put_contents($filePath, $stub) === false) {
                throw new \RuntimeException(\sprintf('Failed to write file: %s', $filePath));
            }
        } else {
            $this->rewriteClassDocblock($filePath, $docBlock);
        }

        $shapeDir = $requestDir . '/Shape';
        if (!is_dir($shapeDir) && !mkdir($shapeDir) && !is_dir($shapeDir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $shapeDir));
        }

        $filenames = $docBlockResourceRequest->getShapeFilenames($requestDir);

        if (file_put_contents($filenames['sorting'], $docBlockResourceRequest->getSortingShape()->toString($shapeNamespace)) === false) {
            throw new \RuntimeException('Failed to write sorting shape file');
        }

        if (file_put_contents($filenames['filtering'], $docBlockResourceRequest->getFilteringShape()->toString($shapeNamespace)) === false) {
            throw new \RuntimeException('Failed to write filtering shape file');
        }
    }

    private function rewriteClassDocblock(string $filePath, string $newDocBlock, bool $allowModifiers = false): void
    {
        $content = file_get_contents($filePath);
        if (!$content) {
            throw new \RuntimeException("Unable to read file: $filePath");
        }

        $classPattern = $allowModifiers
            ? '/^\s*(?:abstract\s+|final\s+)?class\s+([A-Za-z0-9_]+)/m'
            : '/^\s*class\s+([A-Za-z0-9_]+)/m';

        if (!preg_match($classPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            throw new \RuntimeException("Could not find class declaration in $filePath");
        }

        $classOffset = (int)$matches[0][1];
        $beforeClass = preg_replace('/\/\*\*(?:[^*]|\*(?!\/))*\*\//s', '', substr($content, 0, $classOffset));
        $afterClass = substr($content, $classOffset);

        if (file_put_contents($filePath, rtrim($beforeClass) . "\n\n" . $newDocBlock . "\n" . ltrim($afterClass)) === false) {
            throw new \RuntimeException(\sprintf('Failed to write file: %s', $filePath));
        }
    }
}
