<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\OAuthFlowObject;

class OAuthFlowObjectTest extends TestCase
{
    public function testToArray(): void
    {
        $flow = new OAuthFlowObject(
            'https://example.com/auth',
            'https://example.com/token',
            'https://example.com/refresh',
            ['read' => 'Read access']
        );
        
        $expected = [
            'authorizationUrl' => 'https://example.com/auth',
            'tokenUrl' => 'https://example.com/token',
            'refreshUrl' => 'https://example.com/refresh',
            'scopes' => ['read' => 'Read access']
        ];

        $this->assertEquals($expected, $flow->toArray());
        $this->assertEquals('https://example.com/auth', $flow->getAuthorizationUrl());
        $this->assertEquals('https://example.com/token', $flow->getTokenUrl());
        $this->assertEquals('https://example.com/refresh', $flow->getRefreshUrl());
        $this->assertEquals(['read' => 'Read access'], $flow->getScopes());
    }

    public function testToArrayMinimal(): void
    {
        $flow = new OAuthFlowObject(
            'https://example.com/auth',
            'https://example.com/token'
        );
        
        $expected = [
            'authorizationUrl' => 'https://example.com/auth',
            'tokenUrl' => 'https://example.com/token',
            'scopes' => []
        ];

        $this->assertEquals($expected, $flow->toArray());
    }
}
