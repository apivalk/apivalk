<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Stub;

use apivalk\apivalk\Http\Controller\Resource\AbstractCreateResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceCreatedResponse;
use apivalk\apivalk\Router\Route\Route;

class CreateAnimalController extends AbstractCreateResourceController
{
    public static function getRoute(): Route
    {
        return Route::post('/api/v1/animals')
            ->description('Create animal')
            ->tags(self::getEmptyResource()->tags());
    }

    public static function getResourceClass(): string
    {
        return AnimalResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        return new ResourceCreatedResponse(AnimalResource::byRequest($request));
    }
}
