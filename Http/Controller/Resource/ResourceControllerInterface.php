<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Resource\AbstractResource;

/**
 * @template TResource of AbstractResource
 */
interface ResourceControllerInterface
{
    /** @return class-string<AbstractResource> */
    public static function getResourceClass(): string;

    public static function getMode(): string;
}
