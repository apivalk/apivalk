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
    public static function getDescription(): string
    {
        return \sprintf('Create %s', self::getEmptyResource()->getName());
    }

    public static function getMode(): string
    {
        return AbstractResource::MODE_CREATE;
    }

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

    /** @return TResource */
    public function getResource(ApivalkRequestInterface $apivalkRequest): AbstractResource
    {
        /** @var class-string<AbstractResource> $resourceClass */
        $resourceClass = static::getResourceClass();

        return $resourceClass::byRequest($apivalkRequest);
    }
}
