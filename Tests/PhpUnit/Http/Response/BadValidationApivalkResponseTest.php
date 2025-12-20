<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Http\Response\ErrorObject;

class BadValidationApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $error = new ErrorObject('email', 'Invalid email');
        $response = new BadValidationApivalkResponse([$error]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals([$error], $response->getErrors());
        
        $expected = [
            'errors' => ['email' => 'Invalid email']
        ];
        $this->assertEquals($expected, $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = BadValidationApivalkResponse::getDocumentation();
        $this->assertEquals('Request validation failed', $doc->getDescription());
        $this->assertCount(1, $doc->getProperties());
    }
}
