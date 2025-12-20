<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\BadRequestApivalkResponse;

class BadRequestApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new BadRequestApivalkResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = BadRequestApivalkResponse::getDocumentation();
        $this->assertNotEmpty($doc->getDescription());
    }
}
