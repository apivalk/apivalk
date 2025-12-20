<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\MethodNotAllowedApivalkResponse;

class MethodNotAllowedApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new MethodNotAllowedApivalkResponse();
        $this->assertEquals(405, $response->getStatusCode());
        $this->assertArrayHasKey('error', $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = MethodNotAllowedApivalkResponse::getDocumentation();
        $this->assertNotEmpty($doc->getDescription());
    }
}
