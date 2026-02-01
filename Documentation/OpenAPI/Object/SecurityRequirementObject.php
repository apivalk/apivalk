<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class SecurityRequirementObject
 *
 * @see     https://swagger.io/specification/#security-requirement-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class SecurityRequirementObject implements ObjectInterface
{
    /** @var string|null */
    private $securitySchemeName;
    /** @var string[] */
    private $scopes;

    /**
     * @param string|null $securitySchemeName Null means "no security required" (OpenAPI {})
     * @param string[]    $scopes
     */
    function __construct(?string $securitySchemeName = null, array $scopes = [])
    {
        $this->securitySchemeName = $securitySchemeName;
        $this->scopes = $scopes;
    }

    public function getSecuritySchemeName(): ?string
    {
        return $this->securitySchemeName;
    }

    public function isPublicEndpoint(): bool
    {
        return $this->securitySchemeName === null;
    }

    /** @return string[] */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function toArray(): array
    {
        if ($this->isPublicEndpoint()) {
            return [];
        }

        $scopes = [];

        foreach ($this->scopes as $scope) {
            $scopes[] = $scope;
        }

        return [
            $this->securitySchemeName => $scopes
        ];
    }
}
