<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;

/**
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerUpdateRequestQueryShape query()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerUpdateRequestPathShape path()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerUpdateRequestBodyShape body()
 * @method \apivalk\apivalk\Router\Route\Sort\SortBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerUpdateRequestSortingShape sorting()
 * @method \apivalk\apivalk\Router\Route\Filter\FilterBag|\Tests\Integration\RealWorld\Customer\Request\Shape\CustomerUpdateRequestFilteringShape filtering()
 * @method \null paginator()
 */
class CustomerUpdateRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        $doc = new ApivalkRequestDocumentation();

        $doc->addBodyProperty(
            (new StringProperty('first_name', 'First name'))->setMinLength(1)->setMaxLength(100)
        );
        $doc->addBodyProperty(
            (new StringProperty('last_name', 'Last name'))->setMinLength(1)->setMaxLength(100)
        );
        $doc->addBodyProperty(
            (new StringProperty('email', 'Email address'))->setPattern('/.+@.+\..+/')
        );
        $doc->addBodyProperty(
            (new StringProperty('phone', 'Phone number'))->setMaxLength(20)->setIsRequired(false)
        );
        $doc->addBodyProperty(
            new EnumProperty('status', 'Customer status', ['active', 'inactive', 'pending'])
        );

        return $doc;
    }
}
