<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\NotFoundApivalkResponse;
use apivalk\apivalk\Http\Response\MethodNotAllowedApivalkResponse;

class ResponseTest extends TestCase
{
    public function testNotFoundResponse(): void
    {
        $response = new NotFoundApivalkResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testMethodNotAllowedResponse(): void
    {
        $response = new MethodNotAllowedApivalkResponse();
        $this->assertEquals(405, $response->getStatusCode());
    }
}
