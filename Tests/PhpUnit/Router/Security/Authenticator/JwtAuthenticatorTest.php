<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Security\Authenticator;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Security\Authenticator\JwtAuthenticator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\TestCase;

class JwtAuthenticatorTest extends TestCase
{
    private const JWK_SET_URL = 'https://example.com/jwks.json';
    private const ISSUER = 'https://example.com/';
    private const AUDIENCE = 'my-api';
    private const SIGNING_KEY_ID = 'test-key';
    private const SIGNING_SECRET = 'a-test-signing-secret-of-32-byte';

    public function testGetKeysUsesCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $jwks = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'n' => '...',
                    'alg' => 'RS256',
                    'e' => 'AQAB',
                    'kid' => '1'
                ]
            ]
        ];
        $cacheItem = new CacheItem('jwks_' . md5(self::JWK_SET_URL), $jwks);

        $cache->expects($this->once())
            ->method('get')
            ->willReturn($cacheItem);

        $authenticator = new JwtAuthenticator(self::JWK_SET_URL, $cache, self::ISSUER, self::AUDIENCE);

        // We use reflection to call private getJwksKeys
        $reflection = new \ReflectionClass(JwtAuthenticator::class);
        $method = $reflection->getMethod('getJwksKeys');
        $method->setAccessible(true);

        $keys = $method->invoke($authenticator);

        $this->assertIsArray($keys);
        $this->assertArrayHasKey('1', $keys);
        $this->assertInstanceOf(Key::class, $keys['1']);
    }

    public function testTokenMissingIssAndAudIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'sub' => 'attacker',
                'scope' => 'admin:everything'
            ]
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testTokenMissingAudIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'sub' => 'user-123'
            ]
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testTokenWithMatchingIssAndAudIsAccepted(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => self::AUDIENCE,
                'sub' => 'user-123'
            ]
        );

        $identity = $authenticator->authenticate($token);

        $this->assertNotNull($identity);
        $this->assertSame('user-123', $identity->getSub());
    }

    public function testUnconfiguredIssuerAndAudienceAreNotEnforced(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey('', '');

        $token = $this->signWithTrustedKey(['sub' => 'user-123']);

        $this->assertNotNull($authenticator->authenticate($token));
    }

    public function testTokenWithForeignIssuerIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => 'https://attacker.example/',
                'aud' => self::AUDIENCE,
                'sub' => 'user-123'
            ]
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testTokenWithForeignAudienceIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => 'other-api',
                'sub' => 'user-123'
            ]
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testTokenWithAudienceListWithoutTheExpectedAudienceIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => ['other-api', 'third-api'],
                'sub' => 'user-123'
            ]
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testTokenSignedWithAnUntrustedSecretIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->sign(
            [
                'iss' => self::ISSUER,
                'aud' => self::AUDIENCE,
                'sub' => 'user-123'
            ],
            'an-attacker-signing-secret-32-by',
            self::SIGNING_KEY_ID
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testTokenWithUnknownKeyIdIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->sign(
            [
                'iss' => self::ISSUER,
                'aud' => self::AUDIENCE,
                'sub' => 'user-123'
            ],
            self::SIGNING_SECRET,
            'unknown-key'
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testExpiredTokenIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => self::AUDIENCE,
                'sub' => 'user-123',
                'exp' => time() - 60
            ]
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testTokenThatIsNotYetValidIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => self::AUDIENCE,
                'sub' => 'user-123',
                'nbf' => time() + 600
            ]
        );

        $this->assertNull($authenticator->authenticate($token));
    }

    public function testMalformedTokenIsRejected(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $this->assertNull($authenticator->authenticate('not-a-jwt'));
    }

    public function testClaimsOfAnAcceptedTokenArePropagatedToTheIdentity(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => self::AUDIENCE,
                'sub' => 'user-123',
                'username' => 'jane',
                'email' => 'jane@example.com',
                'scope' => 'read write',
                'permissions' => ['user:read', 'user:write']
            ]
        );

        $identity = $authenticator->authenticate($token);

        $this->assertNotNull($identity);
        $this->assertTrue($identity->isAuthenticated());
        $this->assertSame('jane', $identity->getUsername());
        $this->assertSame('jane@example.com', $identity->getEmail());
        $this->assertSame('user-123', $identity->getSub());
        $this->assertSame([self::AUDIENCE], $identity->getAud());
        $this->assertSame(['read', 'write'], $identity->getScopes());
        $this->assertSame(['user:read', 'user:write'], $identity->getPermissions());
    }

    public function testScopesAndPermissionsAreReadFromTheAlternativeClaims(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => self::AUDIENCE,
                'scp' => ['read', 'write read'],
                'roles' => 'admin support'
            ]
        );

        $identity = $authenticator->authenticate($token);

        $this->assertNotNull($identity);
        $this->assertSame(['read', 'write'], $identity->getScopes());
        $this->assertSame(['admin', 'support'], $identity->getPermissions());
    }

    public function testTokenWithoutScopeAndPermissionClaimsYieldsAnIdentityWithoutGrants(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => self::AUDIENCE,
                'sub' => 'user-123'
            ]
        );

        $identity = $authenticator->authenticate($token);

        $this->assertNotNull($identity);
        $this->assertSame([], $identity->getScopes());
        $this->assertSame([], $identity->getPermissions());
    }

    public function testAudClaimIsPropagatedEvenWhenTheAudienceCheckIsDisabled(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, '');

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => 'some-other-api',
                'sub' => 'user-123'
            ]
        );

        $identity = $authenticator->authenticate($token);

        $this->assertNotNull($identity);
        $this->assertSame(['some-other-api'], $identity->getAud());
    }

    public function testTokenWithAudienceListContainingTheExpectedAudienceIsAccepted(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, self::AUDIENCE);

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'aud' => ['other-api', self::AUDIENCE],
                'sub' => 'user-123'
            ]
        );

        $identity = $authenticator->authenticate($token);

        $this->assertNotNull($identity);
        $this->assertSame(['other-api', self::AUDIENCE], $identity->getAud());
        $this->assertTrue($identity->isAudGranted(self::AUDIENCE));
        $this->assertFalse($identity->isAudGranted('third-api'));
    }

    public function testIdentityOfATokenWithoutAudClaimCarriesNoAudience(): void
    {
        $authenticator = $this->authenticatorWithTrustedKey(self::ISSUER, '');

        $token = $this->signWithTrustedKey(
            [
                'iss' => self::ISSUER,
                'sub' => 'user-123'
            ]
        );

        $identity = $authenticator->authenticate($token);

        $this->assertNotNull($identity);
        $this->assertSame([], $identity->getAud());
    }

    private function authenticatorWithTrustedKey(string $issuer, string $audience): JwtAuthenticator
    {
        $authenticator = new JwtAuthenticator(self::JWK_SET_URL, null, $issuer, $audience);

        $keys = new \ReflectionProperty(JwtAuthenticator::class, 'keys');
        $keys->setAccessible(true);
        $keys->setValue($authenticator, [self::SIGNING_KEY_ID => new Key(self::SIGNING_SECRET, 'HS256')]);

        return $authenticator;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function signWithTrustedKey(array $payload): string
    {
        return $this->sign($payload, self::SIGNING_SECRET, self::SIGNING_KEY_ID);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function sign(array $payload, string $secret, string $keyId): string
    {
        return JWT::encode($payload, $secret, 'HS256', $keyId);
    }
}
