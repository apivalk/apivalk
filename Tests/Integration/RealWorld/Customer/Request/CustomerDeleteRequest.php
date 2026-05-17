<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;

/**
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerDeleteRequestQueryShape query()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerDeleteRequestPathShape path()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerDeleteRequestBodyShape body()
 * @method \apivalk\apivalk\Router\Route\Sort\SortBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerDeleteRequestSortingShape sorting()
 * @method \apivalk\apivalk\Router\Route\Filter\FilterBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerDeleteRequestFilteringShape filtering()
 * @method \null paginator()
 */
class CustomerDeleteRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}
