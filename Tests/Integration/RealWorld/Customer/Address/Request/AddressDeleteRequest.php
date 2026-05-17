<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;

/**
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressDeleteRequestQueryShape query()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressDeleteRequestPathShape path()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressDeleteRequestBodyShape body()
 * @method \apivalk\apivalk\Router\Route\Sort\SortBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressDeleteRequestSortingShape sorting()
 * @method \apivalk\apivalk\Router\Route\Filter\FilterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressDeleteRequestFilteringShape filtering()
 * @method \null paginator()
 */
class AddressDeleteRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}
