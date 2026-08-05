<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\File;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\File\File;

class FileTest extends TestCase
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

    public function testGetters(): void
    {
        $file = new File('name.txt', 'text/plain', '/tmp/php123', UPLOAD_ERR_OK, 1024);

        $this->assertEquals('name.txt', $file->getName());
        $this->assertEquals('text/plain', $file->getType());
        $this->assertEquals('/tmp/php123', $file->getTmpName());
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
        $this->assertEquals(1024, $file->getSize());
        $this->assertTrue($file->isValid());
        $this->assertNull($file->getFieldName());
    }

    public function testInvalid(): void
    {
        $file = new File('name.txt', 'text/plain', '/tmp/php123', UPLOAD_ERR_INI_SIZE, 0);
        $this->assertFalse($file->isValid());
        $this->assertEquals(UPLOAD_ERR_INI_SIZE, $file->getError());
    }

    public function testFieldName(): void
    {
        $file = new File('avatar.png', 'image/png', '/tmp/php123', UPLOAD_ERR_OK, 1024, 'avatar');

        $this->assertEquals('avatar', $file->getFieldName());
    }

    public function testGetContentsReturnsTheUploadedBytes(): void
    {
        $tmpFile = $this->createTmpFile('%PDF-1.7 contents');
        $file = new File('invoice.pdf', 'application/pdf', $tmpFile, UPLOAD_ERR_OK, 17, 'file');

        $this->assertEquals('%PDF-1.7 contents', $file->getContents());
    }

    public function testGetContentsThrowsForAFailedUpload(): void
    {
        $file = new File('invoice.pdf', 'application/pdf', '/tmp/php123', UPLOAD_ERR_INI_SIZE, 0, 'file');

        $this->expectException(\RuntimeException::class);

        $file->getContents();
    }

    public function testGetContentsThrowsForAnUnreadableFile(): void
    {
        $file = new File('invoice.pdf', 'application/pdf', '/tmp/does-not-exist-42', UPLOAD_ERR_OK, 10, 'file');

        $this->expectException(\RuntimeException::class);

        $file->getContents();
    }

    public function testGetDetectedMediaTypeIgnoresTheClientSuppliedType(): void
    {
        $tmpFile = $this->createTmpFile('%PDF-1.7 contents');
        $file = new File('invoice.pdf', 'text/plain', $tmpFile, UPLOAD_ERR_OK, 17, 'file');

        $this->assertEquals('application/pdf', $file->getDetectedMediaType());
    }

    public function testGetDetectedMediaTypeIsNullForAFailedUpload(): void
    {
        $file = new File('invoice.pdf', 'application/pdf', '/tmp/php123', UPLOAD_ERR_NO_FILE, 0, 'file');

        $this->assertNull($file->getDetectedMediaType());
    }

    private function createTmpFile(string $contents): string
    {
        $tmpFile = (string)tempnam(sys_get_temp_dir(), 'apivalk-file-test');
        file_put_contents($tmpFile, $contents);

        $this->tmpFiles[] = $tmpFile;

        return $tmpFile;
    }
}
