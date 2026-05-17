<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Request\Shape;

/**
 * @property-read \apivalk\apivalk\Router\Route\Filter\IntegerFilter $customer_id
 * @property-read \apivalk\apivalk\Router\Route\Filter\EnumFilter $status
 * @property-read \apivalk\apivalk\Router\Route\Filter\StringFilter $title
 * @property-read \apivalk\apivalk\Router\Route\Filter\FloatFilter $value
 * @property-read \apivalk\apivalk\Router\Route\Filter\DateFilter $start_date
 */
interface ContractListFilteringShape
{
}