<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Address\Request;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;

/**
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressCreateRequestQueryShape query()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressCreateRequestPathShape path()
 * @method \apivalk\apivalk\Http\Request\Parameter\ParameterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressCreateRequestBodyShape body()
 * @method \apivalk\apivalk\Router\Route\Sort\SortBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressCreateRequestSortingShape sorting()
 * @method \apivalk\apivalk\Router\Route\Filter\FilterBag|\Tests\Integration\RealWorld\Customer\Address\Request\Shape\AddressCreateRequestFilteringShape filtering()
 * @method \null paginator()
 */
class AddressCreateRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        $doc = new ApivalkRequestDocumentation();

        $doc->addBodyProperty(
            (new StringProperty('street', 'Street address'))->setMinLength(1)->setMaxLength(255)
        );
        $doc->addBodyProperty(
            (new StringProperty('city', 'City'))->setMinLength(1)->setMaxLength(100)
        );
        $doc->addBodyProperty(
            (new StringProperty('zip', 'Zip/postal code'))->setMinLength(1)->setMaxLength(20)
        );
        $doc->addBodyProperty(
            (new StringProperty('country', 'ISO 3166-1 alpha-2 country code'))->setMinLength(2)->setMaxLength(2)
        );
        $doc->addBodyProperty(
            new EnumProperty('type', 'Address type', ['billing', 'shipping', 'both'])
        );
        $doc->addBodyProperty(
            (new BooleanProperty('is_primary', 'Primary address flag', false))->setIsRequired(false)
        );

        return $doc;
    }
}
