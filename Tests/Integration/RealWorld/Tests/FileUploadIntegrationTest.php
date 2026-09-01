<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Tests;

use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use PHPUnit\Framework\TestCase;
use Tests\Integration\RealWorld\Bootstrap\RequestTrait;
use Tests\Integration\RealWorld\Customer\Document\Request\DocumentUploadRequest;

/**
 * Drives a real multipart request through the whole stack: routing, authentication, security, file validation
 * and the controller reading the upload from the file bag by its form field name.
 */
class FileUploadIntegrationTest extends TestCase
{
    use RequestTrait;

    private const PATH = '/v1/api/customers/42/documents';
    private const PDF_CONTENTS = '%PDF-1.7 integration test contents';

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

        parent::tearDown();
    }

    public function testUpload_validPdf_returns201WithTheUploadedFileData(): void
    {
        $response = $this->makeRequest(
            'POST',
            self::PATH,
            [],
            ['document_type' => 'invoice'],
            'admin-token',
            '127.0.0.1',
            ['file' => $this->uploadedFile(self::PDF_CONTENTS)]
        );

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(
            [
                'customer_id' => 42,
                'document_type' => 'invoice',
                'field_name' => 'file',
                'filename' => 'invoice.pdf',
                'size' => \strlen(self::PDF_CONTENTS),
                'detected_media_type' => 'application/pdf',
                'contents_md5' => md5(self::PDF_CONTENTS),
            ],
            $response->toArray()['data']
        );
    }

    public function testUpload_withoutFile_returns422(): void
    {
        $response = $this->makeRequest(
            'POST',
            self::PATH,
            [],
            ['document_type' => 'invoice'],
            'admin-token'
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            [['parameter' => 'file', 'message' => 'This field is required.', 'key' => ValidatorResult::FIELD_IS_REQUIRED]],
            $response->toArray()['errors']
        );
    }

    /**
     * A client may claim any Content-Type, so the whitelist has to work off the detected media type.
     */
    public function testUpload_pdfClaimedButPlainTextSent_returns422(): void
    {
        $response = $this->makeRequest(
            'POST',
            self::PATH,
            [],
            ['document_type' => 'invoice'],
            'admin-token',
            '127.0.0.1',
            ['file' => $this->uploadedFile('plain text disguised as a pdf')]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            ValidatorResult::FILE_MEDIA_TYPE_IS_NOT_ALLOWED,
            $response->toArray()['errors'][0]['key']
        );
    }

    public function testUpload_fileLargerThanTheDeclaredMaximum_returns422(): void
    {
        $contents = '%PDF-1.7 ' . str_repeat('x', DocumentUploadRequest::MAX_SIZE_IN_BYTES);

        $response = $this->makeRequest(
            'POST',
            self::PATH,
            [],
            ['document_type' => 'invoice'],
            'admin-token',
            '127.0.0.1',
            ['file' => $this->uploadedFile($contents)]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            ValidatorResult::FILE_IS_LARGER_THAN_MAX_SIZE,
            $response->toArray()['errors'][0]['key']
        );
    }

    /**
     * PHP aborts uploads over its own ini limit and only reports it through the upload error.
     */
    public function testUpload_phpIniSizeLimitExceeded_returns422(): void
    {
        $file = $this->uploadedFile(self::PDF_CONTENTS);
        $file['error'] = UPLOAD_ERR_INI_SIZE;
        $file['size'] = 0;

        $response = $this->makeRequest(
            'POST',
            self::PATH,
            [],
            ['document_type' => 'invoice'],
            'admin-token',
            '127.0.0.1',
            ['file' => $file]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            ValidatorResult::FILE_IS_LARGER_THAN_MAX_SIZE,
            $response->toArray()['errors'][0]['key']
        );
    }

    public function testUpload_invalidBodyFieldAlongsideAValidFile_returns422(): void
    {
        $response = $this->makeRequest(
            'POST',
            self::PATH,
            [],
            ['document_type' => 'not-a-known-type'],
            'admin-token',
            '127.0.0.1',
            ['file' => $this->uploadedFile(self::PDF_CONTENTS)]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('document_type', $response->toArray()['errors'][0]['parameter']);
    }

    public function testUpload_withoutToken_returns401(): void
    {
        $response = $this->makeRequest(
            'POST',
            self::PATH,
            [],
            ['document_type' => 'invoice'],
            null,
            '127.0.0.1',
            ['file' => $this->uploadedFile(self::PDF_CONTENTS)]
        );

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUpload_withoutTheRequiredPermission_returns403(): void
    {
        $response = $this->makeRequest(
            'POST',
            self::PATH,
            [],
            ['document_type' => 'invoice'],
            'read-only-token',
            '127.0.0.1',
            ['file' => $this->uploadedFile(self::PDF_CONTENTS)]
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * @return array<string, mixed>
     */
    private function uploadedFile(string $contents): array
    {
        $tmpFile = (string)tempnam(sys_get_temp_dir(), 'apivalk-upload-integration');
        file_put_contents($tmpFile, $contents);

        $this->tmpFiles[] = $tmpFile;

        return [
            'name' => 'invoice.pdf',
            'type' => 'application/pdf',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => \strlen($contents),
        ];
    }
}
