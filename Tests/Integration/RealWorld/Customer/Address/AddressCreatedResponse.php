<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class AddressCreatedResponse extends AbstractApivalkResponse
{
    /** @var array<string, mixed> */
    private $address;

    /** @param array<string, mixed> $address */
    public function __construct(array $address)
    {
        $this->address = $address;
    }

    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        return new ApivalkResponseDocumentation();
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_201_CREATED;
    }

    public function toArray(): array
    {
        return ['data' => $this->address];
    }
}
