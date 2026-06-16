<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\Property\SimpleArrayProperty;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class CustomerCreatedResponse extends AbstractApivalkResponse
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
        $doc = new ApivalkResponseDocumentation();
        $doc->setDescription('Create customer response');
        $doc->addProperty(
            new SimpleArrayProperty('roles', 'Role names assigned to the customer', SimpleArrayProperty::TYPE_STRING)
        );
        $doc->addProperty(
            new SimpleArrayProperty('permission_ids', 'Granted permission IDs', SimpleArrayProperty::TYPE_INT)
        );
        return $doc;
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_201_CREATED;
    }

    public function toArray(): array
    {
        return ['data' => $this->customer];
    }
}
