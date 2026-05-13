<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Stub;

use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\Resource\AbstractUpdateResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceUpdatedResponse;
use apivalk\apivalk\Router\Route\Route;

class UpdateAnimalController extends AbstractUpdateResourceController
{
    public static function getRoute(): Route
    {
        return Route::patch('/api/v1/animals/{animal_uuid}')
            ->pathProperty(new StringProperty('animal_uuid', 'Unique identifier of the animal'))
            ->description('Update animal')
            ->tags(self::getEmptyResource()->tags());
    }

    public static function getResourceClass(): string
    {
        return AnimalResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $animal = AnimalResource::byRequest($request);
        $animal->animal_uuid = $request->path()->get('animal_uuid')->getValue();

        return new ResourceUpdatedResponse($animal);
    }
}
