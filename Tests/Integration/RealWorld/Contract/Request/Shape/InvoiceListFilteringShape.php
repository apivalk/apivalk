<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Contract\Request\Shape;

/**
 * @property-read \apivalk\apivalk\Router\Route\Filter\EnumFilter $status
 * @property-read \apivalk\apivalk\Router\Route\Filter\FloatFilter $amount
 * @property-read \apivalk\apivalk\Router\Route\Filter\DateFilter $due_date
 * @property-read \apivalk\apivalk\Router\Route\Filter\DateTimeFilter $paid_at
 */
interface InvoiceListFilteringShape
{
}