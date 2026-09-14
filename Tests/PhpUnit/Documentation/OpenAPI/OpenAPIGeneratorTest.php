<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI;

use apivalk\apivalk\Apivalk;
use apivalk\apivalk\Documentation\OpenAPI\Object\ComponentsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\ServerObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\TagObject;
use apivalk\apivalk\Documentation\OpenAPI\OpenAPIGenerator;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Router\AbstractRouter;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class OpenAPIGeneratorTest extends TestCase
{
    public function testGenerateJson(): void
    {
        $route = new Route('/test', new GetMethod());
        
        $controllerClass = $this->defineTestController();

        $router = $this->createMock(AbstractRouter::class);
        $router->method('getRoutes')->willReturn([
            ['route' => $route, 'controllerClass' => $controllerClass]
        ]);

        $apivalk = $this->createMock(Apivalk::class);
        $apivalk->method('getRouter')->willReturn($router);

        $info = new InfoObject('Title', '1.0.0');
        $server = new ServerObject('http://localhost');
        $components = new ComponentsObject();

        $generator = new OpenAPIGenerator($apivalk, $info, [$server], $components);
        
        $json = $generator->generate();
        $this->assertIsString($json);
        
        $data = json_decode($json, true);
        $this->assertEquals('3.2.0', $data['openapi']);
        $this->assertEquals('Title', $data['info']['title']);
        $this->assertEquals('http://localhost', $data['servers'][0]['url']);
        $this->assertArrayHasKey('/test', $data['paths']);
    }

    public function testExcludedRoutesAreOmittedFromPaths(): void
    {
        $controllerClass = $this->defineTestController();

        $router = $this->createMock(AbstractRouter::class);
        $router->method('getRoutes')->willReturn([
            ['route' => new Route('/test', new GetMethod()), 'controllerClass' => $controllerClass],
            [
                'route' => Route::get('/internal')->excludeFromDocumentation(),
                'controllerClass' => $controllerClass
            ],
        ]);

        $apivalk = $this->createMock(Apivalk::class);
        $apivalk->method('getRouter')->willReturn($router);

        $json = (new OpenAPIGenerator($apivalk, new InfoObject('Title', '1.0.0')))->generate();
        $data = json_decode($json, true);

        $this->assertArrayHasKey('/test', $data['paths']);
        $this->assertArrayNotHasKey('/internal', $data['paths']);
    }

    public function testExcludedRoutesAreDocumentedWhenForceIncluded(): void
    {
        $controllerClass = $this->defineTestController();

        $generator = $this->createGeneratorForRoutes([
            ['route' => new Route('/test', new GetMethod()), 'controllerClass' => $controllerClass],
            [
                'route' => Route::get('/internal')->excludeFromDocumentation(),
                'controllerClass' => $controllerClass
            ],
        ]);

        $data = json_decode($generator->forceIncludeExcludedRoutes()->generate(), true);

        $this->assertArrayHasKey('/test', $data['paths']);
        $this->assertArrayHasKey('/internal', $data['paths']);
    }

    public function testOnlyRoutesWithTheGivenTagsAreDocumented(): void
    {
        $controllerClass = $this->defineTestController();

        $generator = $this->createGeneratorForRoutes([
            [
                'route' => Route::get('/animals')->tags([new TagObject('Animals')]),
                'controllerClass' => $controllerClass
            ],
            [
                'route' => Route::get('/contracts')->tags([new TagObject('Contracts')]),
                'controllerClass' => $controllerClass
            ],
            ['route' => Route::get('/untagged'), 'controllerClass' => $controllerClass],
        ]);

        $data = json_decode($generator->onlyWithTags(['Animals'])->generate(), true);

        $this->assertSame(['/animals'], array_keys($data['paths']));
    }

    public function testTagFilterDoesNotDocumentExcludedRoutes(): void
    {
        $controllerClass = $this->defineTestController();

        $generator = $this->createGeneratorForRoutes([
            [
                'route' => Route::get('/animals')->tags([new TagObject('Animals')]),
                'controllerClass' => $controllerClass
            ],
            [
                'route' => Route::get('/internal/animals')
                    ->tags([new TagObject('Animals')])
                    ->excludeFromDocumentation(),
                'controllerClass' => $controllerClass
            ],
        ]);

        $data = json_decode($generator->onlyWithTags(['Animals'])->generate(), true);

        $this->assertSame(['/animals'], array_keys($data['paths']));
    }

    public function testForceIncludeAndTagFilterCombine(): void
    {
        $controllerClass = $this->defineTestController();

        $generator = $this->createGeneratorForRoutes([
            [
                'route' => Route::get('/animals')->tags([new TagObject('Animals')]),
                'controllerClass' => $controllerClass
            ],
            [
                'route' => Route::get('/internal/animals')
                    ->tags([new TagObject('Animals')])
                    ->excludeFromDocumentation(),
                'controllerClass' => $controllerClass
            ],
            [
                'route' => Route::get('/contracts')->tags([new TagObject('Contracts')]),
                'controllerClass' => $controllerClass
            ],
        ]);

        $data = json_decode(
            $generator->forceIncludeExcludedRoutes()->onlyWithTags(['Animals'])->generate(),
            true
        );

        $this->assertSame(['/animals', '/internal/animals'], array_keys($data['paths']));
    }

    public function testEachGenerateCallStartsFromTheCurrentOptions(): void
    {
        $controllerClass = $this->defineTestController();

        $generator = $this->createGeneratorForRoutes([
            ['route' => new Route('/test', new GetMethod()), 'controllerClass' => $controllerClass],
            [
                'route' => Route::get('/internal')->excludeFromDocumentation(),
                'controllerClass' => $controllerClass
            ],
        ]);

        $internal = json_decode($generator->forceIncludeExcludedRoutes(true)->generate(), true);
        $public = json_decode($generator->forceIncludeExcludedRoutes(false)->generate(), true);

        $this->assertSame(['/test', '/internal'], array_keys($internal['paths']));
        $this->assertSame(['/test'], array_keys($public['paths']));
    }

    public function testTagCarriedOnlyByAnExcludedRouteIsRejected(): void
    {
        $controllerClass = $this->defineTestController();

        $generator = $this->createGeneratorForRoutes([
            [
                'route' => Route::get('/animals')->tags([new TagObject('Animals')]),
                'controllerClass' => $controllerClass
            ],
            [
                'route' => Route::get('/internal')
                    ->tags([new TagObject('Internal')])
                    ->excludeFromDocumentation(),
                'controllerClass' => $controllerClass
            ],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown OpenAPI tag(s) "Internal"');

        $generator->onlyWithTags(['Internal'])->generate();
    }

    public function testUnknownTagNameIsRejectedInsteadOfProducingAnEmptyDocument(): void
    {
        $controllerClass = $this->defineTestController();

        $generator = $this->createGeneratorForRoutes([
            [
                'route' => Route::get('/animals')->tags([new TagObject('Animals')]),
                'controllerClass' => $controllerClass
            ],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown OpenAPI tag(s) "animals"');

        $generator->onlyWithTags(['animals'])->generate();
    }

    public function testEmptyTagNameIsRejected(): void
    {
        $generator = new OpenAPIGenerator($this->createMock(Apivalk::class));

        $this->expectException(\InvalidArgumentException::class);

        $generator->onlyWithTags(['']);
    }

    public function testGenerateUnsupportedFormat(): void
    {
        $apivalk = $this->createMock(Apivalk::class);
        $generator = new OpenAPIGenerator($apivalk);

        $this->expectException(\InvalidArgumentException::class);
        $generator->generate('yaml');
    }

    /**
     * @param array<int, array{route: Route, controllerClass: string}> $routes
     */
    private function createGeneratorForRoutes(array $routes): OpenAPIGenerator
    {
        $router = $this->createMock(AbstractRouter::class);
        $router->method('getRoutes')->willReturn($routes);

        $apivalk = $this->createMock(Apivalk::class);
        $apivalk->method('getRouter')->willReturn($router);

        return new OpenAPIGenerator($apivalk, new InfoObject('Title', '1.0.0'));
    }

    private function defineTestController(): string
    {
        if (!class_exists('TestControllerForOpenAPI')) {
            eval(
            '
                class TestRequestForOpenAPI extends apivalk\apivalk\Http\Request\AbstractApivalkRequest {
                    public static function getDocumentation(): apivalk\apivalk\Documentation\ApivalkRequestDocumentation {
                        return new apivalk\apivalk\Documentation\ApivalkRequestDocumentation();
                    }
                }

                class TestControllerForOpenAPI extends apivalk\apivalk\Http\Controller\AbstractApivalkController {
                public function __invoke(\apivalk\apivalk\Http\Request\ApivalkRequestInterface $request): \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
                    $response = new class extends \apivalk\apivalk\Http\Response\AbstractApivalkResponse {
                        public static function getDocumentation(): \apivalk\apivalk\Documentation\ApivalkResponseDocumentation { return new \apivalk\apivalk\Documentation\ApivalkResponseDocumentation(); }
                        public static function getStatusCode(): int { return 200; }
                        public function toArray(): array { return []; }
                    };
                    return $response;
                }
                public static function getRoute(): \apivalk\apivalk\Router\Route\Route { return new \apivalk\apivalk\Router\Route\Route("/test", new \apivalk\apivalk\Http\Method\GetMethod()); }
                public static function getRequestClass(): string { return "TestRequestForOpenAPI"; }
                public static function getResponseClasses(): array { return []; }
            }'
            );
        }

        return 'TestControllerForOpenAPI';
    }
}
