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
    /** @var string */
    private $name;
    /** @var string[] */
    private $scopes;

    function __construct(string $name, array $scopes = [])
    {
        $this->name = $name;
        $this->scopes = $scopes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function toArray(): array
    {
        return [
            $this->name => $this->scopes
        ];
    }
}
