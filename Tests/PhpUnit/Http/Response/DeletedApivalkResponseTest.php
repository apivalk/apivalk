<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\DeletedApivalkResponse;

class DeletedApivalkResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new DeletedApivalkResponse();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals([], $response->toArray());
    }

    public function testGetDocumentation(): void
    {
        $doc = DeletedApivalkResponse::getDocumentation();
        $this->assertEquals('Deleted', $doc->getDescription());
    }
}
