<?php

declare(strict_types=1);

namespace apivalk\apivalk\Util;

final class ClassLocator
{
    private $path;
    private $namespace;

    public function __construct(string $path, string $namespace)
    {
        $this->path = $path;
        $this->namespace = $namespace;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return array{className: string, path: string}
     */
    public function findClasses(): array
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->getPath()));
        $filePaths = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $basePath = rtrim($this->getPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $filePath = $file->getRealPath();

            $relativePath = $this->getRelativePath($basePath, $filePath);
            $classPath = substr($relativePath, 0, -4);

            $classNamespacePart = str_replace('/', '\\', $classPath);
            $className = rtrim($this->getNamespace(), '\\') . '\\' . ltrim($classNamespacePart, '\\');

            class_exists($className);

            $filePaths[$className] = $filePath;
        }

        $allClasses = get_declared_classes();

        $filteredClasses = array_filter($allClasses, function ($class) {
            return strpos($class, $this->getNamespace()) === 0;
        });

        $classes = [];
        foreach ($filteredClasses as $class) {
            if (!\array_key_exists($class, $filePaths)) {
                continue;
            }

            $classes[] = ['className' => $class, 'path' => $filePaths[$class]];
        }

        return $classes;
    }

    private function normalizePath(string $path): string
    {
        $parts = [];
        $normalized = str_replace('\\', '/', $path);
        $hasLeadingSlash = isset($normalized[0]) && $normalized[0] === '/';
        $segments = explode('/', $normalized);

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);
            } else {
                $parts[] = $segment;
            }
        }

        $prefix = $hasLeadingSlash ? '/' : '';

        return $prefix . implode('/', $parts);
    }

    private function getRelativePath(string $base, string $full): string
    {
        $normalizedBase = $this->normalizePath($base);
        $normalizedFull = $this->normalizePath($full);

        $escapedBase = preg_quote($normalizedBase, '#');

        return preg_replace("#^{$escapedBase}#", '', $normalizedFull);
    }
}
