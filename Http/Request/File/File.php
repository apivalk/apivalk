<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\File;

class File
{
    /** @var string */
    private $name;
    /** @var string */
    private $type;
    /** @var string */
    private $tmpName;
    /** @var int - See: UPLOAD_ERR_* PHP constants */
    private $error;
    /** @var int */
    private $size;

    public function __construct(string $name, string $type, string $tmpName, int $error, int $size)
    {
        $this->name = $name;
        $this->type = $type;
        $this->tmpName = $tmpName;
        $this->error = $error;
        $this->size = $size;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    /** @see UPLOAD_ERR_ int PHP upload error constants */
    public function getError(): int
    {
        return $this->error;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }
}
