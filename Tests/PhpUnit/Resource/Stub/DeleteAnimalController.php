<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Stub;

use apivalk\apivalk\Http\Controller\Resource\AbstractDeleteResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\DeletedApivalkResponse;

class DeleteAnimalController extends AbstractDeleteResourceController
{
    public static function getResourceClass(): string
    {
        return AnimalResource::class;
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        return new DeletedApivalkResponse();
    }
}
