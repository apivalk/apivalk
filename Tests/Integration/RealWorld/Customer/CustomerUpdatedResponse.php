<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class CustomerUpdatedResponse extends AbstractApivalkResponse
{
    /** @var array<string, mixed> */
    private $customer;

    /** @param array<string, mixed> $customer */
    public function __construct(array $customer)
    {
        $this->customer = $customer;
    }

    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        return new ApivalkResponseDocumentation();
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_200_OK;
    }

    public function toArray(): array
    {
        return ['data' => $this->customer];
    }
}
