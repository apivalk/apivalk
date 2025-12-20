<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\File;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\File\File;

class FileTest extends TestCase
{
    public function testGetters(): void
    {
        $file = new File('name.txt', 'text/plain', '/tmp/php123', UPLOAD_ERR_OK, 1024);

        $this->assertEquals('name.txt', $file->getName());
        $this->assertEquals('text/plain', $file->getType());
        $this->assertEquals('/tmp/php123', $file->getTmpName());
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
        $this->assertEquals(1024, $file->getSize());
        $this->assertTrue($file->isValid());
    }

    public function testInvalid(): void
    {
        $file = new File('name.txt', 'text/plain', '/tmp/php123', UPLOAD_ERR_INI_SIZE, 0);
        $this->assertFalse($file->isValid());
        $this->assertEquals(UPLOAD_ERR_INI_SIZE, $file->getError());
    }
}
