<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class AddressListResponse extends AbstractApivalkResponse
{
    /** @var array<int, array<string, mixed>> */
    private $addresses;

    /** @param array<int, array<string, mixed>> $addresses */
    public function __construct(array $addresses)
    {
        $this->addresses = $addresses;
    }

    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        $doc = new ApivalkResponseDocumentation();
        $doc->setDescription('List addresses response');
        return $doc;
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_200_OK;
    }

    public function toArray(): array
    {
        return ['data' => $this->addresses];
    }
}
