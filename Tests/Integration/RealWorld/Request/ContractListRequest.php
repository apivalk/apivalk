<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Request;

/**
 * @method \apivalk\apivalk\Router\Route\Sort\SortBag|\Tests\Integration\RealWorld\Request\Shape\ContractListSortingShape sorting()
 * @method \apivalk\apivalk\Router\Route\Filter\FilterBag|\Tests\Integration\RealWorld\Request\Shape\ContractListFilteringShape filtering()
 * @method \apivalk\apivalk\Http\Request\Pagination\CursorPaginator paginator()
 */
class ContractListRequest extends \apivalk\apivalk\Http\Request\Resource\ResourceRequest
{
}