<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Stub;

use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\Resource\AbstractViewResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceViewResponse;
use apivalk\apivalk\Router\Route\Route;

class ViewAnimalController extends AbstractViewResourceController
{
    protected static function buildRoute(): Route
    {
        return Route::get('/api/v1/animals/{animal_uuid}')
            ->pathProperty(new StringProperty('animal_uuid', 'Unique identifier of the animal'))
            ->description('Get animal');
    }

    public static function getResourceClass(): string
    {
        return AnimalResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $animal = AnimalResource::byArray([
            'animal_uuid' => $request->path()->animal_uuid,
        ]);

        return new ResourceViewResponse($animal);
    }
}
