<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Request\Shape;

/**
 * @property-read \apivalk\apivalk\Router\Route\Filter\StringFilter $first_name
 * @property-read \apivalk\apivalk\Router\Route\Filter\StringFilter $last_name
 * @property-read \apivalk\apivalk\Router\Route\Filter\StringFilter $email
 * @property-read \apivalk\apivalk\Router\Route\Filter\EnumFilter $status
 */
interface CustomerListRequestFilteringShape
{
}