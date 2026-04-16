<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Controller\Resource;

use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Resource\ResourceRequest;
use apivalk\apivalk\Http\Response\BadRequestApivalkResponse;
use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceViewResponse;
use apivalk\apivalk\Resource\AbstractResource;

/**
 * @template TResource of AbstractResource
 * @extends AbstractResourceController<TResource>
 */
abstract class AbstractViewResourceController extends AbstractResourceController
{
    public static function getDescription(): string
    {
        return \sprintf('Get %s', self::getEmptyResource()->getName());
    }

    public static function getMode(): string
    {
        return AbstractResource::MODE_VIEW;
    }

    public static function getRequestClass(): string
    {
        return ResourceRequest::class;
    }

    public function getResourceIdentifier(ApivalkRequestInterface $request): ?string
    {
        $identifierPropertyName = self::getEmptyResource()->getIdentifierProperty()->getPropertyName();
        $identifierParameter = $request->path()->get($identifierPropertyName);

        if ($identifierParameter === null) {
            throw new \InvalidArgumentException(\sprintf('Missing path parameter "%s"', $identifierPropertyName));
        }

        return $identifierParameter->getValue();
    }

    public static function getResponseClasses(): array
    {
        return [
            ResourceViewResponse::class,
            BadRequestApivalkResponse::class,
            ForbiddenApivalkResponse::class,
        ];
    }
}
