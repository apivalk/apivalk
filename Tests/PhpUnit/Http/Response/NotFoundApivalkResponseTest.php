<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;

class NotFoundApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new NotFoundApivalkResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['error' => 'Not found'], $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = NotFoundApivalkResponse::getDocumentation();
        $this->assertEquals('Not found', $doc->getDescription());
    }
}
