<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation;

class FileResponseDocumentation extends ApivalkResponseDocumentation
{
    private string $mediaType;

    public function __construct(string $mediaType = 'application/octet-stream', ?string $description = null)
    {
        $this->mediaType = $mediaType;

        if ($description !== null) {
            $this->setDescription($description);
        }
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }
}
