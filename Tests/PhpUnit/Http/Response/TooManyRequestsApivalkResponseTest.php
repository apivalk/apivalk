<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\TooManyRequestsApivalkResponse;

class TooManyRequestsApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new TooManyRequestsApivalkResponse();
        $this->assertEquals(429, $response->getStatusCode());
        $this->assertArrayHasKey('error', $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = TooManyRequestsApivalkResponse::getDocumentation();
        $this->assertNotEmpty($doc->getDescription());
    }
}
