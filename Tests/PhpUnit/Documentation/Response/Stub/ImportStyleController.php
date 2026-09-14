<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Response\Stub;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse as Missing;
use apivalk\apivalk\Http\Response\{BadRequestApivalkResponse, DeletedApivalkResponse};
use apivalk\apivalk\Router\Route\Route;

/**
 * Every way a response class can be written: aliased import, group import, fully qualified name
 * and a class from the controller's own namespace.
 */
class ImportStyleController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/import-style', new GetMethod());
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        if ($request === null) {
            return new Missing();
        }

        if ($request === false) {
            return new BadRequestApivalkResponse();
        }

        if ($request === true) {
            return new \apivalk\apivalk\Http\Response\TooManyRequestsApivalkResponse();
        }

        if ($request === 0) {
            return new StubSuccessResponse();
        }

        return new DeletedApivalkResponse();
    }
}
