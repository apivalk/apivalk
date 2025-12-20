<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class InfoObject
 *
 * @see     https://swagger.io/specification/#info-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class InfoObject implements ObjectInterface
{
    /** @var string */
    private $title;
    /** @var string|null $summary */
    private $summary;
    /** @var string|null */
    private $description;
    /** @var string|null */
    private $termsOfService;
    /** @var ContactObject|null */
    private $contact = null;
    /** @var LicenseObject|null */
    private $license = null;
    /** @var string */
    private $version;

    public function __construct(
        string $title,
        string $version,
        ?string $summary = null,
        ?string $description = null,
        ?string $termsOfService = null,
        ?ContactObject $contact = null,
        ?LicenseObject $license = null
    ) {
        $this->title = $title;
        $this->version = $version;
        $this->summary = $summary;
        $this->description = $description;
        $this->termsOfService = $termsOfService;
        $this->contact = $contact;
        $this->license = $license;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTermsOfService(): ?string
    {
        return $this->termsOfService;
    }

    public function getContact(): ?ContactObject
    {
        return $this->contact;
    }

    public function getLicense(): ?LicenseObject
    {
        return $this->license;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'title' => $this->title,
                'summary' => $this->summary,
                'version' => $this->version,
                'description' => $this->description,
                'termsOfService' => $this->termsOfService,
                'contact' => $this->contact instanceof ContactObject ? array_filter($this->contact->toArray()) : null,
                'license' => $this->license instanceof LicenseObject ? array_filter($this->license->toArray()) : null,
            ]
        );
    }
}
