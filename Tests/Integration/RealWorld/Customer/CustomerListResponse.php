<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class CustomerListResponse extends AbstractApivalkResponse
{
    /** @var array<int, array<string, mixed>> */
    private $customers;

    /** @param array<int, array<string, mixed>> $customers */
    public function __construct(array $customers)
    {
        $this->customers = $customers;
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
        return ['data' => $this->customers];
    }
}
