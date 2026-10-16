<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation;

use apivalk\apivalk\Documentation\FileResponseDocumentation;
use PHPUnit\Framework\TestCase;

class FileResponseDocumentationTest extends TestCase
{
    public function testDefaults(): void
    {
        $documentation = new FileResponseDocumentation();

        $this->assertSame('application/octet-stream', $documentation->getMediaType());
        $this->assertNull($documentation->getDescription());
        $this->assertSame([], $documentation->getProperties());
    }

    public function testMediaTypeAndDescription(): void
    {
        $documentation = new FileResponseDocumentation('application/pdf', 'Invoice as PDF');

        $this->assertSame('application/pdf', $documentation->getMediaType());
        $this->assertSame('Invoice as PDF', $documentation->getDescription());
    }
}
