<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
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

    /**
     * Build a resource instance from the validated request body, with any path parameters
     * whose names match a resource property automatically set on the resource.
     *
     * Example: route has {animal_uuid} and AnimalResource declares an animal_uuid property —
     * after this call $resource->animal_uuid is already populated from the path.
     *
     * @return TResource
     */
    protected function getResource(ApivalkRequestInterface $request): AbstractResource
    {
        $resourceClass = static::getResourceClass();
        /** @var AbstractResource $resource */
        $resource = $resourceClass::byRequest($request);

        foreach ($request->path()->getIterator() as $pathParam) {
            if ($resource->hasProperty($pathParam->getName())) {
                $resource->__set($pathParam->getName(), $pathParam->getValue());
            }
        }

        return $resource;
    }
}
