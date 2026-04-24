<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Stub;

use apivalk\apivalk\Http\Controller\Resource\AbstractListResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationPaginationResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceListResponse;
use apivalk\apivalk\Router\Route\Pagination\Pagination;

class ListAnimalsController extends AbstractListResourceController
{
    public static function getResourceClass(): string
    {
        return AnimalResource::class;
    }

    public static function pagination(): ?Pagination
    {
        return Pagination::page()->setMaxLimit(25);
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        return new ResourceListResponse([], new PagePaginationPaginationResponse(1, 25, false, 0));
    }
}
