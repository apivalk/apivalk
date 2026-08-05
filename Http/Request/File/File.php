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
    /** @var string|null - Name of the form field the file was uploaded with */
    private $fieldName;

    public function __construct(
        string $name,
        string $type,
        string $tmpName,
        int $error,
        int $size,
        ?string $fieldName = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->tmpName = $tmpName;
        $this->error = $error;
        $this->size = $size;
        $this->fieldName = $fieldName;
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

    /** Is null when the file was not created from an uploaded form field. */
    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    /**
     * The type reported by getType() is supplied by the client and must not be trusted, so the media type is
     * detected from the file contents instead. Returns null when detection is unavailable or fails.
     */
    public function getDetectedMediaType(): ?string
    {
        if (!$this->isValid() || !function_exists('finfo_open')) {
            return null;
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($fileInfo === false) {
            return null;
        }

        $mediaType = finfo_file($fileInfo, $this->tmpName);
        finfo_close($fileInfo);

        return $mediaType === false ? null : $mediaType;
    }

    /**
     * @throws \RuntimeException
     */
    public function getContents(): string
    {
        if (!$this->isValid()) {
            throw new \RuntimeException(
                \sprintf('Upload of file "%s" failed with error code %d', $this->name, $this->error)
            );
        }

        if (!is_readable($this->tmpName)) {
            throw new \RuntimeException(
                \sprintf('Uploaded file "%s" is not readable at "%s"', $this->name, $this->tmpName)
            );
        }

        $contents = file_get_contents($this->tmpName);

        if ($contents === false) {
            throw new \RuntimeException(\sprintf('Contents of uploaded file "%s" could not be read', $this->name));
        }

        return $contents;
    }
}
