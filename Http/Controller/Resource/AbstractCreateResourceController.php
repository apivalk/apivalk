<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Http\Response\BadRequestApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceCreatedResponse;
use apivalk\apivalk\Resource\AbstractResource;

/**
 * @template TResource of AbstractResource
 * @extends AbstractResourceController<TResource>
 */
abstract class AbstractCreateResourceController extends AbstractResourceController
{
    public static function getRequestClass(): string
    {
        return ResourceRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [
            ResourceCreatedResponse::class,
            BadRequestApivalkResponse::class,
            ForbiddenApivalkResponse::class,
        ];
    }

    /**
     * Build a resource instance from the validated request body.
     * Path parameters are not included — on create the identifier does not exist yet.
     *
     * @return TResource
     */
    protected function getResource(ApivalkRequestInterface $request): AbstractResource
    {
        $resourceClass = static::getResourceClass();

        return $resourceClass::byRequest($request);
    }
}
