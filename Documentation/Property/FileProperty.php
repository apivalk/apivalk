<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

/**
 * Declares an uploaded file of a multipart/form-data request. Unlike the other properties the value does not live
 * in a parameter bag but in the request's file bag, so it is validated by the FileValidator instead of the
 * parameter based validators.
 *
 * @see \apivalk\apivalk\Documentation\Property\Validator\FileValidator
 * @see \apivalk\apivalk\Documentation\ApivalkRequestDocumentation::addFileProperty
 */
class FileProperty extends AbstractProperty
{
    private ?int $maxSizeInBytes = null;
    /** @var string[] */
    private array $allowedMediaTypes = [];

    public function getType(): string
    {
        return 'string';
    }

    public function getFormat(): string
    {
        return 'binary';
    }

    public function getPhpType(): string
    {
        return 'string';
    }

    public function setMaxSizeInBytes(?int $maxSizeInBytes): self
    {
        $this->maxSizeInBytes = $maxSizeInBytes;

        return $this;
    }

    public function getMaxSizeInBytes(): ?int
    {
        return $this->maxSizeInBytes;
    }

    /**
     * The media types accepted for this file, e.g. ['application/pdf']. An empty list accepts every media type.
     *
     * @param string[] $allowedMediaTypes
     */
    public function setAllowedMediaTypes(array $allowedMediaTypes): self
    {
        $this->allowedMediaTypes = $allowedMediaTypes;

        return $this;
    }

    /** @return string[] */
    public function getAllowedMediaTypes(): array
    {
        return $this->allowedMediaTypes;
    }

    /** @return array<string, string|int|null> */
    public function getDocumentationArray(): array
    {
        $array = [
            'type' => $this->getType(),
            'format' => $this->getFormat(),
        ];

        if ($this->getMaxSizeInBytes() !== null) {
            $array['maxLength'] = $this->getMaxSizeInBytes();
        }

        if (\count($this->getAllowedMediaTypes()) === 1) {
            $array['contentMediaType'] = $this->getAllowedMediaTypes()[0];
        }

        if ($this->getPropertyDescription() !== '') {
            $array['description'] = $this->getPropertyDescription();
        }

        if ($this->getExample() !== null) {
            $array['example'] = $this->getExample();
        }

        return $array;
    }
}
