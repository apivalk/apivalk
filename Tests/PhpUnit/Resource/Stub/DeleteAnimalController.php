<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Stub;

use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\Resource\AbstractDeleteResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\DeletedApivalkResponse;
use apivalk\apivalk\Router\Route\Route;

class DeleteAnimalController extends AbstractDeleteResourceController
{
    public static function getRoute(): Route
    {
        return Route::delete('/api/v1/animals/{animal_uuid}')
            ->pathProperty(new StringProperty('animal_uuid', 'Unique identifier of the animal'))
            ->description('Delete animal')
            ->tags(self::getEmptyResource()->tags());
    }

    public static function getResourceClass(): string
    {
        return AnimalResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        return new DeletedApivalkResponse();
    }
}
