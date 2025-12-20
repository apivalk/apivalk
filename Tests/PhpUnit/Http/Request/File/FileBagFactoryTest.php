<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\File;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\File\FileBagFactory;

class FileBagFactoryTest extends TestCase
{
    private $filesBackup;

    protected function setUp(): void
    {
        $this->filesBackup = $_FILES;
        $_FILES = [];
    }

    protected function tearDown(): void
    {
        $_FILES = $this->filesBackup;
    }

    public function testCreateEmpty(): void
    {
        $bag = FileBagFactory::create();
        $this->assertCount(0, $bag);
    }

    public function testCreateWithFiles(): void
    {
        $_FILES = [
            'avatar' => [
                'name' => 'avatar.png',
                'type' => 'image/png',
                'tmp_name' => '/tmp/php1',
                'error' => UPLOAD_ERR_OK,
                'size' => 100
            ],
            'documents' => [
                'name' => ['doc1.pdf', 'doc2.pdf'],
                'type' => ['application/pdf', 'application/pdf'],
                'tmp_name' => ['/tmp/php2', '/tmp/php3'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [200, 300]
            ]
        ];

        $bag = FileBagFactory::create();
        $this->assertCount(3, $bag);
        $this->assertTrue($bag->has('avatar.png'));
        $this->assertTrue($bag->has('doc1.pdf'));
        $this->assertTrue($bag->has('doc2.pdf'));
    }

    public function testNormalizeUploadedFilesSingle(): void
    {
        $data = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/test',
            'error' => 0,
            'size' => 10
        ];
        $normalized = FileBagFactory::normalizeUploadedFiles($data);
        $this->assertCount(1, $normalized);
        $this->assertEquals($data, $normalized[0]);
    }

    public function testNormalizeUploadedFilesMultiple(): void
    {
        $data = [
            'name' => ['f1', 'f2'],
            'type' => ['t1', 't2'],
            'tmp_name' => ['tmp1', 'tmp2'],
            'error' => [0, 0],
            'size' => [1, 2]
        ];
        $normalized = FileBagFactory::normalizeUploadedFiles($data);
        $this->assertCount(2, $normalized);
        $this->assertEquals('f1', $normalized[0]['name']);
        $this->assertEquals('f2', $normalized[1]['name']);
    }
}
