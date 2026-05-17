<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;

/**
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressViewRequestQueryShape query()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressViewRequestPathShape path()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressViewRequestBodyShape body()
 * @method \apivalk\apivalk\Router\Route\Sort\SortBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressViewRequestSortingShape sorting()
 * @method \apivalk\apivalk\Router\Route\Filter\FilterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressViewRequestFilteringShape filtering()
 * @method \null paginator()
 */
class AddressViewRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}
