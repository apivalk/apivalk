<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\UnauthorizedApivalkResponse;

class UnauthorizedApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new UnauthorizedApivalkResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertArrayHasKey('error', $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = UnauthorizedApivalkResponse::getDocumentation();
        $this->assertNotEmpty($doc->getDescription());
    }
}
