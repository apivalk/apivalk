<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\File;

class FileBag implements \IteratorAggregate, \Countable
{
    /** @var File[] */
    private $files = [];

    /**
     * Files are keyed by the form field they were uploaded with. The explicit key is only needed for a field
     * carrying several files, where the factory disambiguates them.
     */
    public function set(File $file, ?string $key = null): void
    {
        $this->files[$key ?? $file->getFieldName() ?? $file->getName()] = $file;
    }

    public function has(string $key): bool
    {
        return isset($this->files[$key]);
    }

    public function get(string $key): ?File
    {
        return $this->files[$key] ?? null;
    }

    /** @return \Iterator<string, File> */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->files);
    }

    public function count(): int
    {
        return \count($this->files);
    }

    /**
     * Magic getter to directly access a file of the bag, mirroring ParameterBag. Unlike a parameter there is no
     * value to unwrap, so the File itself is returned.
     *
     * $fileBag->file is the same as $fileBag->get('file')
     */
    public function __get(string $key): ?File
    {
        return $this->get($key);
    }
}
