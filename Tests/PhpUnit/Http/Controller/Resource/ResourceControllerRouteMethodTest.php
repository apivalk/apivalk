<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Controller\Resource;

use apivalk\apivalk\Http\Controller\Resource\AbstractCreateResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractDeleteResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractListResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractUpdateResourceController;
use apivalk\apivalk\Http\Controller\Resource\AbstractViewResourceController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\DeletedApivalkResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceCreatedResponse;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceListResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceUpdatedResponse;
use apivalk\apivalk\Http\Response\Resource\ResourceViewResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\CreateAnimalController;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\DeleteAnimalController;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\ListAnimalsController;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\UpdateAnimalController;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\ViewAnimalController;
use PHPUnit\Framework\TestCase;

class ResourceControllerRouteMethodTest extends TestCase
{
    /**
     * @return array<string, array{class-string<AbstractResourceController<AnimalResource>>, string}>
     */
    public function stubControllerProvider(): array
    {
        return [
            'create' => [CreateAnimalController::class, 'POST'],
            'view' => [ViewAnimalController::class, 'GET'],
            'update' => [UpdateAnimalController::class, 'PATCH'],
            'delete' => [DeleteAnimalController::class, 'DELETE'],
            'list' => [ListAnimalsController::class, 'GET'],
        ];
    }

    /**
     * @dataProvider stubControllerProvider
     *
     * @param class-string<AbstractResourceController<AnimalResource>> $controllerClass
     */
    public function testMatchingRouteMethodIsAccepted(string $controllerClass, string $expectedMethod): void
    {
        $this->assertEquals($expectedMethod, $controllerClass::getRoute()->getMethod()->getName());
    }

    public function testCreateAcceptsPutForFullReplace(): void
    {
        $controllerClass = get_class(new class extends AbstractCreateResourceController {
            protected static function buildRoute(): Route
            {
                return Route::put('/api/v1/animals/{animal_uuid}');
            }

            public static function getResourceClass(): string
            {
                return AnimalResource::class;
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new ResourceCreatedResponse($this->getResource($request));
            }
        });

        $this->assertEquals('PUT', $controllerClass::getRoute()->getMethod()->getName());
    }

    public function testCreateRejectsPatch(): void
    {
        $controllerClass = get_class(new class extends AbstractCreateResourceController {
            protected static function buildRoute(): Route
            {
                return Route::patch('/api/v1/animals');
            }

            public static function getResourceClass(): string
            {
                return AnimalResource::class;
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new ResourceCreatedResponse($this->getResource($request));
            }
        });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(
            '/must return a POST or PUT route from buildRoute\(\), got "PATCH" for "\/api\/v1\/animals"/'
        );

        $controllerClass::getRoute();
    }

    public function testUpdateRejectsPut(): void
    {
        $controllerClass = get_class(new class extends AbstractUpdateResourceController {
            protected static function buildRoute(): Route
            {
                return Route::put('/api/v1/animals/{animal_uuid}');
            }

            public static function getResourceClass(): string
            {
                return AnimalResource::class;
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new ResourceUpdatedResponse($this->getResource($request));
            }
        });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(
            '/must return a PATCH route from buildRoute\(\), got "PUT" for "\/api\/v1\/animals\/\{animal_uuid\}"/'
        );

        $controllerClass::getRoute();
    }

    public function testViewRejectsPost(): void
    {
        $controllerClass = get_class(new class extends AbstractViewResourceController {
            protected static function buildRoute(): Route
            {
                return Route::post('/api/v1/animals/{animal_uuid}');
            }

            public static function getResourceClass(): string
            {
                return AnimalResource::class;
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new ResourceViewResponse(new AnimalResource());
            }
        });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must return a GET route from buildRoute\(\), got "POST"/');

        $controllerClass::getRoute();
    }

    public function testDeleteRejectsGet(): void
    {
        $controllerClass = get_class(new class extends AbstractDeleteResourceController {
            protected static function buildRoute(): Route
            {
                return Route::get('/api/v1/animals/{animal_uuid}');
            }

            public static function getResourceClass(): string
            {
                return AnimalResource::class;
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new DeletedApivalkResponse();
            }
        });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must return a DELETE route from buildRoute\(\), got "GET"/');

        $controllerClass::getRoute();
    }

    public function testListRejectsPost(): void
    {
        $controllerClass = get_class(new class extends AbstractListResourceController {
            protected static function buildRoute(): Route
            {
                return Route::post('/api/v1/animals');
            }

            public static function getResourceClass(): string
            {
                return AnimalResource::class;
            }

            public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
            {
                return new ResourceListResponse([], new PagePaginationResponse(1, 25, false));
            }
        });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must return a GET route from buildRoute\(\), got "POST"/');

        $controllerClass::getRoute();
    }
}
