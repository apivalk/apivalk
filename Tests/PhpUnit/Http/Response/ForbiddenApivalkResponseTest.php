<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use apivalk\apivalk\Http\Response\ForbiddenApivalkResponse;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use PHPUnit\Framework\TestCase;

class ForbiddenApivalkResponseTest extends TestCase
{
    public function testGetDocumentation(): void
    {
        $documentation = ForbiddenApivalkResponse::getDocumentation();
        $this->assertInstanceOf(ApivalkResponseDocumentation::class, $documentation);
        $this->assertEquals('Forbidden', $documentation->getDescription());
    }

    public function testGetStatusCode(): void
    {
        $this->assertEquals(403, ForbiddenApivalkResponse::getStatusCode());
    }

    public function testToArray(): void
    {
        $response = new ForbiddenApivalkResponse();
        $this->assertEquals(['error' => 'Forbidden'], $response->toArray());
    }
}
