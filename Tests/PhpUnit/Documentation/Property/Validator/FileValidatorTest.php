<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\FileProperty;
use apivalk\apivalk\Documentation\Property\Validator\FileValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Http\Request\File\File;

class FileValidatorTest extends TestCase
{
    /** @var string[] */
    private array $tmpFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }

        $this->tmpFiles = [];
    }

    public function testValidFile(): void
    {
        $property = new FileProperty('file');
        $validator = new FileValidator($property);

        $this->assertSame($property, $validator->getProperty());
        $this->assertTrue($validator->validate($this->createFile('%PDF-1.7 contents'))->isSuccess());
    }

    public function testFailedUpload(): void
    {
        $validator = new FileValidator(new FileProperty('file'));
        $file = new File('invoice.pdf', 'application/pdf', '/tmp/php123', UPLOAD_ERR_PARTIAL, 0, 'file');

        $result = $validator->validate($file);

        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::FILE_UPLOAD_FAILED, $result->getErrorKey());
    }

    /**
     * PHP reports its own limits through the upload error, so those must read as a size problem.
     */
    public function testPhpSizeLimitIsReportedAsASizeError(): void
    {
        $validator = new FileValidator(new FileProperty('file'));
        $file = new File('invoice.pdf', 'application/pdf', '/tmp/php123', UPLOAD_ERR_INI_SIZE, 0, 'file');

        $result = $validator->validate($file);

        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::FILE_IS_LARGER_THAN_MAX_SIZE, $result->getErrorKey());
    }

    public function testMaxSizeInBytes(): void
    {
        $property = new FileProperty('file');
        $property->setMaxSizeInBytes(17);
        $validator = new FileValidator($property);

        $this->assertTrue($validator->validate($this->createFile('%PDF-1.7 contents'))->isSuccess());

        $property->setMaxSizeInBytes(16);
        $result = $validator->validate($this->createFile('%PDF-1.7 contents'));

        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::FILE_IS_LARGER_THAN_MAX_SIZE, $result->getErrorKey());
    }

    public function testAllowedMediaTypes(): void
    {
        $property = new FileProperty('file');
        $property->setAllowedMediaTypes(['application/pdf']);
        $validator = new FileValidator($property);

        $this->assertTrue($validator->validate($this->createFile('%PDF-1.7 contents'))->isSuccess());

        $result = $validator->validate($this->createFile('plain text contents'));

        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::FILE_MEDIA_TYPE_IS_NOT_ALLOWED, $result->getErrorKey());
    }

    /**
     * The client supplied media type must never be able to talk its way past the whitelist.
     */
    public function testAllowedMediaTypesIgnoreTheClientSuppliedType(): void
    {
        $property = new FileProperty('file');
        $property->setAllowedMediaTypes(['application/pdf']);
        $validator = new FileValidator($property);

        $file = $this->createFile('plain text contents', 'application/pdf');
        $result = $validator->validate($file);

        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::FILE_MEDIA_TYPE_IS_NOT_ALLOWED, $result->getErrorKey());
    }

    private function createFile(string $contents, string $clientType = 'application/pdf'): File
    {
        $tmpFile = (string)tempnam(sys_get_temp_dir(), 'apivalk-file-validator-test');
        file_put_contents($tmpFile, $contents);

        $this->tmpFiles[] = $tmpFile;

        return new File('invoice.pdf', $clientType, $tmpFile, UPLOAD_ERR_OK, \strlen($contents), 'file');
    }
}
