<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathItemObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\OperationObject;
use apivalk\apivalk\Http\Method\GetMethod;

class PathItemObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $getOperation = new OperationObject(new GetMethod());
        $pathItem = new PathItemObject('Summary', 'Description', $getOperation);
        
        $result = $pathItem->toArray();

        $this->assertEquals('Summary', $result['summary']);
        $this->assertEquals('Description', $result['description']);
        $this->assertIsArray($result['get']);
        $this->assertNull($result['post']);
    }

    public function testToArrayMinimal(): void
    {
        $pathItem = new PathItemObject();
        $result = $pathItem->toArray();

        $this->assertNull($result['get']);
        $this->assertEmpty($result['parameters']);
    }
}
