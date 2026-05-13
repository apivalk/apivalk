<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Resource\AbstractResource;

/**
 * @template TResource of AbstractResource
 * @implements ResourceControllerInterface<TResource>
 */
abstract class AbstractResourceController extends AbstractApivalkController implements ResourceControllerInterface
{
    /**
     * @return class-string<TResource>
     */
    abstract public static function getResourceClass(): string;

    /**
     * @return TResource
     */
    public static function getEmptyResource(): AbstractResource
    {
        /** @var class-string<TResource> $resourceClass */
        $resourceClass = static::getResourceClass();

        return new $resourceClass();
    }
}
