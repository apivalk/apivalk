<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\InternalServerErrorApivalkResponse;

class InternalServerErrorApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new InternalServerErrorApivalkResponse();
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('error', $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = InternalServerErrorApivalkResponse::getDocumentation();
        $this->assertNotEmpty($doc->getDescription());
    }
}
