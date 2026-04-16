<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Stub;

use apivalk\apivalk\Http\Controller\Resource\AbstractViewResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceViewResponse;

class ViewAnimalController extends AbstractViewResourceController
{
    public static function getResourceClass(): string
    {
        return AnimalResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $animal = AnimalResource::byRequest($request);

        return new ResourceViewResponse($animal);
    }
}
