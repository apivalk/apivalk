<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Middleware;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\FileProperty;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\File\File;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Middleware\RequestValidationMiddleware;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use PHPUnit\Framework\TestCase;

/**
 * Covers the file leg of the validation middleware: declared FileProperties are validated against the request's
 * file bag and produce the same error payload as every other validation error.
 */
class FileValidationTest extends TestCase
{
    /** @var string[] */
    private $tmpFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }

        $this->tmpFiles = [];
    }

    public function testValidUploadPassesThrough(): void
    {
        $property = new FileProperty('file');
        $property->setAllowedMediaTypes(['application/pdf'])->setMaxSizeInBytes(1024);

        $fileBag = new FileBag();
        $fileBag->set($this->createFile('%PDF-1.7 contents'));

        $response = $this->process($property, $fileBag);

        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testMissingRequiredFileIsAValidationError(): void
    {
        $response = $this->process(new FileProperty('file'), new FileBag());

        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        $this->assertSame(
            [['parameter' => 'file', 'message' => 'This field is required.', 'key' => ValidatorResult::FIELD_IS_REQUIRED]],
            $response->toArray()['errors']
        );
    }

    public function testMissingOptionalFileIsAccepted(): void
    {
        $property = new FileProperty('file');
        $property->setIsRequired(false);

        $response = $this->process($property, new FileBag());

        $this->assertNotInstanceOf(BadValidationApivalkResponse::class, $response);
    }

    public function testDisallowedMediaTypeIsAValidationError(): void
    {
        $property = new FileProperty('file');
        $property->setAllowedMediaTypes(['application/pdf']);

        $fileBag = new FileBag();
        $fileBag->set($this->createFile('plain text contents'));

        $response = $this->process($property, $fileBag);

        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        $this->assertSame(
            ValidatorResult::FILE_MEDIA_TYPE_IS_NOT_ALLOWED,
            $response->toArray()['errors'][0]['key']
        );
    }

    public function testOversizedFileIsAValidationError(): void
    {
        $property = new FileProperty('file');
        $property->setMaxSizeInBytes(4);

        $fileBag = new FileBag();
        $fileBag->set($this->createFile('%PDF-1.7 contents'));

        $response = $this->process($property, $fileBag);

        $this->assertInstanceOf(BadValidationApivalkResponse::class, $response);
        $this->assertSame(
            ValidatorResult::FILE_IS_LARGER_THAN_MAX_SIZE,
            $response->toArray()['errors'][0]['key']
        );
    }

    private function process(FileProperty $property, FileBag $fileBag): AbstractApivalkResponse
    {
        $documentation = new ApivalkRequestDocumentation();
        $documentation->addFileProperty($property);

        $filterBag = $this->createMock(FilterBag::class);
        $filterBag->method('getIterator')->willReturn(new \ArrayIterator([]));

        $request = $this->createMock(ApivalkRequestInterface::class);
        $request->method('getRuntimeDocumentation')->willReturn($documentation);
        $request->method('body')->willReturn(new ParameterBag());
        $request->method('query')->willReturn(new ParameterBag());
        $request->method('path')->willReturn(new ParameterBag());
        $request->method('file')->willReturn($fileBag);
        $request->method('filtering')->willReturn($filterBag);

        $next = function (): AbstractApivalkResponse {
            return $this->createMock(AbstractApivalkResponse::class);
        };

        return (new RequestValidationMiddleware())->process(
            $request,
            $this->createMock(AbstractApivalkController::class),
            $next
        );
    }

    private function createFile(string $contents): File
    {
        $tmpFile = (string)tempnam(sys_get_temp_dir(), 'apivalk-file-validation-test');
        file_put_contents($tmpFile, $contents);

        $this->tmpFiles[] = $tmpFile;

        return new File('invoice.pdf', 'application/pdf', $tmpFile, UPLOAD_ERR_OK, \strlen($contents), 'file');
    }
}
