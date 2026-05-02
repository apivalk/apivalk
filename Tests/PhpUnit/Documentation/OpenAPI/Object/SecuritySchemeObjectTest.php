<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\OpenAPI\Object;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\OpenAPI\Object\OAuthFlowsObject;
use apivalk\apivalk\Documentation\OpenAPI\Object\SecuritySchemeObject;

class SecuritySchemeObjectTest extends TestCase
{
    public function testHttpBearerFactory(): void
    {
        $scheme = SecuritySchemeObject::http('bearer', 'bearer', 'OAuth2 Bearer Authorization', 'JWT');

        $this->assertEquals('http', $scheme->getType());
        $this->assertEquals('bearer', $scheme->getName());
        $this->assertEquals('OAuth2 Bearer Authorization', $scheme->getDescription());
        $this->assertEquals('bearer', $scheme->getScheme());
        $this->assertEquals('JWT', $scheme->getBearerFormat());
        $this->assertNull($scheme->getIn());

        $result = $scheme->toArray();
        $this->assertEquals([
            'type'         => 'http',
            'description'  => 'OAuth2 Bearer Authorization',
            'scheme'       => 'bearer',
            'bearerFormat' => 'JWT',
        ], $result);
        $this->assertArrayNotHasKey('name', $result);
        $this->assertArrayNotHasKey('in', $result);
    }

    public function testHttpBasicFactory(): void
    {
        $scheme = SecuritySchemeObject::http('basic', 'basic');

        $result = $scheme->toArray();
        $this->assertEquals(['type' => 'http', 'scheme' => 'basic'], $result);
        $this->assertArrayNotHasKey('name', $result);
        $this->assertArrayNotHasKey('in', $result);
        $this->assertArrayNotHasKey('bearerFormat', $result);
    }

    public function testApiKeyFactory(): void
    {
        $scheme = SecuritySchemeObject::apiKey('api_key', 'header', 'API Key description');

        $this->assertEquals('apiKey', $scheme->getType());
        $this->assertEquals('api_key', $scheme->getName());
        $this->assertEquals('header', $scheme->getIn());

        $result = $scheme->toArray();
        $this->assertEquals([
            'type'        => 'apiKey',
            'description' => 'API Key description',
            'name'        => 'api_key',
            'in'          => 'header',
        ], $result);
        $this->assertArrayNotHasKey('scheme', $result);
        $this->assertArrayNotHasKey('bearerFormat', $result);
    }

    public function testOauth2Factory(): void
    {
        $flows = $this->createMock(OAuthFlowsObject::class);
        $flows->method('toArray')->willReturn(['implicit' => ['authorizationUrl' => 'https://example.com/auth']]);

        $scheme = SecuritySchemeObject::oauth2('oauth2', $flows);

        $this->assertEquals('oauth2', $scheme->getType());
        $this->assertEquals('oauth2', $scheme->getName());
        $this->assertSame($flows, $scheme->getFlows());

        $result = $scheme->toArray();
        $this->assertEquals('oauth2', $result['type']);
        $this->assertArrayHasKey('flows', $result);
        $this->assertArrayNotHasKey('name', $result);
        $this->assertArrayNotHasKey('in', $result);
        $this->assertArrayNotHasKey('scheme', $result);
    }

    public function testOpenIdConnectFactory(): void
    {
        $scheme = SecuritySchemeObject::openIdConnect('oidc', 'https://example.com/.well-known/openid-configuration');

        $this->assertEquals('openIdConnect', $scheme->getType());
        $this->assertEquals('oidc', $scheme->getName());

        $result = $scheme->toArray();
        $this->assertEquals([
            'type'             => 'openIdConnect',
            'openIdConnectUrl' => 'https://example.com/.well-known/openid-configuration',
        ], $result);
        $this->assertArrayNotHasKey('name', $result);
        $this->assertArrayNotHasKey('scheme', $result);
    }

    public function testNameIsPreservedForRouteMatching(): void
    {
        $scheme = SecuritySchemeObject::http('my-auth-scheme', 'bearer');
        $this->assertEquals('my-auth-scheme', $scheme->getName());
        $this->assertArrayNotHasKey('name', $scheme->toArray());
    }

    public function testLegacyConstructorStillWorks(): void
    {
        $scheme = new SecuritySchemeObject('http', 'api', 'OAuth2 Bearer Authorization', 'header', 'bearer', 'JWT', null, null);

        $result = $scheme->toArray();
        $this->assertArrayNotHasKey('name', $result);
        $this->assertArrayNotHasKey('in', $result);
        $this->assertEquals('bearer', $result['scheme']);
        $this->assertEquals('JWT', $result['bearerFormat']);
    }
}
