<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Response;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\Response\ControllerResponseScanner;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\GetMethod;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadRequestApivalkResponse;
use apivalk\apivalk\Http\Response\DeletedApivalkResponse;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationResponse;
use apivalk\apivalk\Http\Response\TooManyRequestsApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Tests\PhpUnit\Documentation\Response\Stub\ImportStyleController;
use apivalk\apivalk\Tests\PhpUnit\Documentation\Response\Stub\StubSuccessResponse;
use PHPUnit\Framework\TestCase;

class ControllerResponseScannerTest extends TestCase
{
    public function testDirectReturnsAreFound(): void
    {
        $this->assertSame(
            [ScannerSuccessResponse::class, NotFoundApivalkResponse::class],
            ControllerResponseScanner::scan(DirectReturnController::class)
        );
    }

    /**
     * A paginated list controller cannot call setPaginationResponse() on a `return new …`
     * expression, so it has to assign first. Following the variable back is what makes the scan
     * usable at all.
     */
    public function testAResponseReturnedThroughAVariableIsFound(): void
    {
        $this->assertSame(
            [ScannerSuccessResponse::class],
            ControllerResponseScanner::scan(PaginatedListController::class)
        );
    }

    /**
     * The pagination metadata of that same idiom is constructed but never returned, and it is not
     * a response class either. Picking up every `new …Response` would document it as one.
     */
    public function testMetadataThatIsNeverReturnedIsNotAResponse(): void
    {
        $this->assertNotContains(
            PagePaginationResponse::class,
            ControllerResponseScanner::scan(PaginatedListController::class)
        );
    }

    public function testAliasedGroupedQualifiedAndLocalNamesAllResolve(): void
    {
        $scanned = ControllerResponseScanner::scan(ImportStyleController::class);
        \sort($scanned);

        $expected = [
            BadRequestApivalkResponse::class,
            DeletedApivalkResponse::class,
            NotFoundApivalkResponse::class,
            StubSuccessResponse::class,
            TooManyRequestsApivalkResponse::class,
        ];
        \sort($expected);

        $this->assertSame($expected, $scanned);
    }

    /**
     * A ternary is ordinary PHP for "found or not found", so both branches have to be documented.
     */
    public function testBothBranchesOfATernaryAreFound(): void
    {
        $scanned = ControllerResponseScanner::scan(TernaryController::class);
        \sort($scanned);

        $expected = [ScannerSuccessResponse::class, NotFoundApivalkResponse::class];
        \sort($expected);

        $this->assertSame($expected, $scanned);
    }

    public function testTheRightHandSideOfANullCoalesceIsFound(): void
    {
        $this->assertContains(
            NotFoundApivalkResponse::class,
            ControllerResponseScanner::scan(CoalesceController::class)
        );
    }

    /**
     * A variable is only traced back when it is the entire returned expression. As an argument it
     * says nothing about what the surrounding call returns.
     */
    public function testAVariableUsedAsAnArgumentIsNotTracedBack(): void
    {
        $this->assertSame(
            [DeletedApivalkResponse::class],
            ControllerResponseScanner::scan(ArgumentController::class)
        );
    }

    public function testAControllerWithoutAnyResponseFailsLoudly(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No response class found');

        ControllerResponseScanner::scan(ThrowingController::class);
    }

    /**
     * An anonymous response has no name to put into an OpenAPI document, so it must not pass
     * silently as "this controller documents nothing".
     */
    public function testAnAnonymousResponseFailsLoudly(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No response class found');

        ControllerResponseScanner::scan(AnonymousResponseController::class);
    }

    public function testAControllerWithoutReadableSourceFailsLoudly(): void
    {
        if (!\class_exists('EvaldScannerController')) {
            eval('
                class EvaldScannerController extends \\apivalk\\apivalk\\Http\\Controller\\AbstractApivalkController {
                    public static function getRoute(): \\apivalk\\apivalk\\Router\\Route\\Route {
                        return new \\apivalk\\apivalk\\Router\\Route\\Route("/evald", new \\apivalk\\apivalk\\Http\\Method\\GetMethod());
                    }
                    public function __invoke(\\apivalk\\apivalk\\Http\\Request\\ApivalkRequestInterface $request): \\apivalk\\apivalk\\Http\\Response\\AbstractApivalkResponse {
                        return new \\apivalk\\apivalk\\Http\\Response\\NotFoundApivalkResponse();
                    }
                }
            ');
        }

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot read the source');

        ControllerResponseScanner::scan('EvaldScannerController');
    }
}

class ScannerRequest extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}

class ScannerSuccessResponse extends AbstractApivalkResponse
{
    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        return new ApivalkResponseDocumentation();
    }

    public static function getStatusCode(): int
    {
        return 200;
    }

    public function toArray(): array
    {
        return [];
    }
}

class DirectReturnController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/direct', new GetMethod());
    }

    public function __invoke(ScannerRequest $request): AbstractApivalkResponse
    {
        if ($request->path()->has('id')) {
            return new ScannerSuccessResponse();
        }

        return new NotFoundApivalkResponse();
    }
}

class PaginatedListController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/paginated', new GetMethod());
    }

    public function __invoke(ScannerRequest $request): AbstractApivalkResponse
    {
        $response = new ScannerSuccessResponse();
        $response->setPaginationResponse(new PagePaginationResponse(0, 25, false, 1));

        return $response;
    }
}

class ThrowingController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/throwing', new GetMethod());
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        throw new \RuntimeException('not implemented');
    }
}

class AnonymousResponseController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/anonymous', new GetMethod());
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        return new class extends AbstractApivalkResponse {
            public static function getDocumentation(): ApivalkResponseDocumentation
            {
                return new ApivalkResponseDocumentation();
            }

            public static function getStatusCode(): int
            {
                return 200;
            }

            public function toArray(): array
            {
                return [];
            }
        };
    }
}

class TernaryController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/ternary', new GetMethod());
    }

    public function __invoke(ScannerRequest $request): AbstractApivalkResponse
    {
        return $request->path()->has('id') ? new ScannerSuccessResponse() : new NotFoundApivalkResponse();
    }
}

class CoalesceController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/coalesce', new GetMethod());
    }

    public function __invoke(ScannerRequest $request): AbstractApivalkResponse
    {
        $found = null;

        return $found ?? new NotFoundApivalkResponse();
    }
}

class ArgumentController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return new Route('/argument', new GetMethod());
    }

    public function __invoke(ScannerRequest $request): AbstractApivalkResponse
    {
        $headers = new ScannerSuccessResponse();

        return $this->withHeaders(new DeletedApivalkResponse(), $headers);
    }

    private function withHeaders(AbstractApivalkResponse $response, AbstractApivalkResponse $other): AbstractApivalkResponse
    {
        return $response;
    }
}
