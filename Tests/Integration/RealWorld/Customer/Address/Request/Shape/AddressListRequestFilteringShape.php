<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address\Request\Shape;

/**
 * @property-read \apivalk\apivalk\Router\Route\Filter\StringFilter $city
 * @property-read \apivalk\apivalk\Router\Route\Filter\StringFilter $country
 * @property-read \apivalk\apivalk\Router\Route\Filter\EnumFilter $type
 * @property-read \apivalk\apivalk\Router\Route\Filter\BooleanFilter $is_primary
 */
interface AddressListRequestFilteringShape
{
}