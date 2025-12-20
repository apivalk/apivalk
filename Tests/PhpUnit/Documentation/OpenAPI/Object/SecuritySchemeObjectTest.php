<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecuritySchemeObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\OAuthFlowsObject;

class SecuritySchemeObjectTest extends TestCase
{
    public function testToArrayApiKey(): void
    {
        $scheme = new SecuritySchemeObject(
            'apiKey',
            'api_key',
            'API Key description',
            'header',
            null,
            null,
            null,
            null
        );
        
        $result = $scheme->toArray();

        $this->assertEquals('apiKey', $result['type']);
        $this->assertEquals('api_key', $result['name']);
        $this->assertEquals('header', $result['in']);
        
        $this->assertEquals('apiKey', $scheme->getType());
        $this->assertEquals('api_key', $scheme->getName());
        $this->assertEquals('API Key description', $scheme->getDescription());
        $this->assertEquals('header', $scheme->getIn());
        $this->assertNull($scheme->getScheme());
        $this->assertNull($scheme->getBearerFormat());
        $this->assertNull($scheme->getFlows());
        $this->assertNull($scheme->getOpenIdConnectUrl());
    }

    public function testToArrayOAuth2(): void
    {
        $flows = $this->createMock(OAuthFlowsObject::class);
        $flows->method('toArray')->willReturn(['implicit' => ['authorizationUrl' => 'https://example.com/auth']]);

        $scheme = new SecuritySchemeObject(
            'oauth2',
            'oauth2',
            null,
            null,
            null,
            null,
            $flows,
            null
        );

        $result = $scheme->toArray();
        $this->assertEquals('oauth2', $result['type']);
        $this->assertArrayHasKey('flows', $result);
        $this->assertSame($flows, $scheme->getFlows());
    }
}
