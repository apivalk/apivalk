<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\File;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\File\FileBag;
use apivalk\apivalk\Http\Request\File\File;

class FileBagTest extends TestCase
{
    public function testBag(): void
    {
        $bag = new FileBag();
        $file = $this->createMock(File::class);
        $file->method('getName')->willReturn('avatar');

        $bag->set($file);

        $this->assertTrue($bag->has('avatar'));
        $this->assertFalse($bag->has('other'));
        $this->assertSame($file, $bag->get('avatar'));
        $this->assertNull($bag->get('other'));
        $this->assertCount(1, $bag);

        foreach ($bag as $name => $f) {
            $this->assertEquals('avatar', $name);
            $this->assertSame($file, $f);
        }
    }

    public function testFieldNameIsPreferredOverTheClientFilename(): void
    {
        $bag = new FileBag();
        $file = $this->createMock(File::class);
        $file->method('getFieldName')->willReturn('avatar');
        $file->method('getName')->willReturn('avatar.png');

        $bag->set($file);

        $this->assertTrue($bag->has('avatar'));
        $this->assertFalse($bag->has('avatar.png'));
    }

    public function testMagicGetterReturnsTheFile(): void
    {
        $bag = new FileBag();
        $file = $this->createMock(File::class);
        $file->method('getFieldName')->willReturn('avatar');

        $bag->set($file);

        $this->assertSame($file, $bag->avatar);
        $this->assertNull($bag->other);
    }

    public function testExplicitKeyWins(): void
    {
        $bag = new FileBag();
        $file = $this->createMock(File::class);
        $file->method('getFieldName')->willReturn('documents');
        $file->method('getName')->willReturn('doc1.pdf');

        $bag->set($file, 'documents[0]');

        $this->assertTrue($bag->has('documents[0]'));
        $this->assertFalse($bag->has('documents'));
        $this->assertSame($file, $bag->get('documents[0]'));
    }
}
