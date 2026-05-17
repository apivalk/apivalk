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
    protected static function buildRoute(): Route
    {
        return Route::patch('/api/v1/animals/{animal_uuid}')
            ->pathProperty(new StringProperty('animal_uuid', 'Unique identifier of the animal'))
            ->description('Update animal');
    }

    public static function getResourceClass(): string
    {
        return AnimalResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        return new ResourceUpdatedResponse($this->getResource($request));
    }
}
