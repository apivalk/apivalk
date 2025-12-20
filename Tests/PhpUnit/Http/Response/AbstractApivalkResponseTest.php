<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\ResponsePagination;
use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;

class AbstractApivalkResponseTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $response = new class extends AbstractApivalkResponse {
            public static function getDocumentation(): ApivalkResponseDocumentation { return new ApivalkResponseDocumentation(); }
            public static function getStatusCode(): int { return 200; }
            public function toArray(): array { return []; }
        };

        $this->assertEquals([], $response->getHeaders());
        $response->setHeaders(['X-Test' => 'Value']);
        $this->assertEquals(['X-Test' => 'Value'], $response->getHeaders());

        $this->assertFalse($response->hasPagination());
        $pagination = $this->createMock(ResponsePagination::class);
        $response->addPagination($pagination);
        $this->assertTrue($response->hasPagination());
        $this->assertSame($pagination, $response->getResponsePagination());
    }
}
