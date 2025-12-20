<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\OpenAPI;
use apivalk\apivalk\Documentation\OpenAPI\Object\InfoObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\PathItemObject;

class OpenAPITest extends TestCase
{
    public function testToArray(): void
    {
        $openApi = new OpenAPI();
        $openApi->setInfo(new InfoObject('Title', '1.0.0'));
        $pathItem = new PathItemObject();
        $openApi->addPaths(new PathsObject('/test', $pathItem));
        
        $result = $openApi->toArray();

        $this->assertEquals('3.1.1', $result['openapi']);
        $this->assertEquals('Title', $result['info']['title']);
        $this->assertArrayHasKey('/test', $result['paths']);
    }

    public function testToJson(): void
    {
        $openApi = new OpenAPI();
        $openApi->setInfo(new InfoObject('Title', '1.0.0'));
        
        $json = $openApi->toJson();
        $this->assertJson($json);
        $this->assertStringContainsString('"openapi":"3.1.1"', $json);
    }
}
