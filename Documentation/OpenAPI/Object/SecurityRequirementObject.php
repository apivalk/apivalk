<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

use apivalk\apivalk\Security\ScopeInterface;

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
    private $name;
    /** @var ScopeInterface[] */
    private $scopes;

    /**
     * @param string|null      $name Null means "no security required" (OpenAPI {})
     * @param ScopeInterface[] $scopes
     */
    function __construct(?string $name = null, array $scopes = [])
    {
        $this->name = $name;
        $this->scopes = $scopes;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isPublicEndpoint(): bool
    {
        return $this->name === null;
    }

    /** @return ScopeInterface[] */
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
            $scopes[] = $scope->getName();
        }

        return [
            $this->name => $scopes
        ];
    }
}
