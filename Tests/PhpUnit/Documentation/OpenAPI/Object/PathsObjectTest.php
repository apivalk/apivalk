<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathItemObject;

class PathsObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $pathItem = new PathItemObject('Summary');
        $paths = new PathsObject('/users', $pathItem);
        
        $result = $paths->toArray();

        $this->assertArrayHasKey('/users', $result);
        $this->assertEquals('Summary', $result['/users']['summary']);
    }
}
