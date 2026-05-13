<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Http\Response\BadRequestApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceUpdatedResponse;
use apivalk\apivalk\Resource\AbstractResource;

/**
 * @template TResource of AbstractResource
 * @extends AbstractResourceController<TResource>
 */
abstract class AbstractUpdateResourceController extends AbstractResourceController
{
    public static function getRequestClass(): string
    {
        return ResourceRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [
            ResourceUpdatedResponse::class,
            BadRequestApivalkResponse::class,
            ForbiddenApivalkResponse::class,
        ];
    }
}
