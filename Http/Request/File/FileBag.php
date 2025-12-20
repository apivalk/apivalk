<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\File;

class FileBag implements \IteratorAggregate, \Countable
{
    /** @var File[] */
    private $files = [];

    public function set(File $file): void
    {
        $this->files[$file->getName()] = $file;
    }

    public function has(string $key): bool
    {
        return isset($this->files[$key]);
    }

    public function get(string $name): ?File
    {
        return $this->files[$name] ?? null;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->files);
    }

    public function count(): int
    {
        return \count($this->files);
    }
}
