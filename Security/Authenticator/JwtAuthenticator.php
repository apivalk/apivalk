<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\Authenticator;

use apivalk\apivalk\Cache\CacheInterface;
use apivalk\apivalk\Cache\CacheItem;
use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\JwtAuthIdentity;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthenticator implements AuthenticatorInterface
{
    /** @var string */
    private $jwkSetUrl;

    /** @var CacheInterface|null */
    private $cache;

    /** @var string */
    private $issuer;

    /** @var string */
    private $audience;

    /** @var array<string, Key>|null */
    private $keys;

    /**
     * @param string              $jwkSetUrl The URL to the JWK Set (e.g. https://example.com/.well-known/jwks.json)
     * @param CacheInterface|null $cache
     * @param string              $issuer    Expected issuer (iss claim)
     * @param string              $audience  Expected audience (aud claim)
     */
    public function __construct(string $jwkSetUrl, ?CacheInterface $cache, string $issuer, string $audience)
    {
        $this->jwkSetUrl = $jwkSetUrl;
        $this->cache = $cache;
        $this->issuer = $issuer;
        $this->audience = $audience;
    }

    public function authenticate(string $token): ?AbstractAuthIdentity
    {
        try {
            $keys = $this->getJwksKeys();
            $payload = (array)JWT::decode($token, $keys);

            if (isset($payload['iss']) && (string)$payload['iss'] !== $this->issuer) {
                return null;
            }

            if (isset($payload['aud']) && !$this->audMatches($payload['aud'], $this->audience)) {
                return null;
            }

            $scopes = $this->parseScopes($payload['scope'] ?? $payload['scp'] ?? null);
            $permissions = $this->extractPermissions($payload);

            return new JwtAuthIdentity(
                isset($payload['username']) ? (string)$payload['username'] : null,
                isset($payload['email']) ? (string)$payload['email'] : null,
                isset($payload['sub']) ? (string)$payload['sub'] : null,
                $scopes,
                $permissions
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param mixed  $audClaim
     * @param string $expectedAudience
     */
    private function audMatches($audClaim, string $expectedAudience): bool
    {
        if (\is_string($audClaim)) {
            return $audClaim === $expectedAudience;
        }

        if (\is_array($audClaim)) {
            foreach ($audClaim as $aud) {
                if ((string)$aud === $expectedAudience) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $scopeClaim
     *
     * @return string[]
     */
    private function parseScopes($scopeClaim): array
    {
        if (\is_array($scopeClaim)) {
            return $this->normalizeStringArray($scopeClaim, true);
        }

        if (\is_string($scopeClaim)) {
            $parts = preg_split('/\s+/', trim($scopeClaim)) ?: [];

            return $this->normalizeStringArray($parts, false);
        }

        return [];
    }

    /**
     * Accepts:
     *
     * In general values: [test, test2] or "test test2"
     *
     * - permissions: array|string
     * - permission: array|string
     * - roles: array|string
     * - role: array|string
     *
     * @param array<string, mixed> $payload
     *
     * @return string[]|null
     */
    private function extractPermissions(array $payload): ?array
    {
        $candidates = [
            $payload['permissions'] ?? null,
            $payload['permission'] ?? null,
            $payload['roles'] ?? null,
            $payload['role'] ?? null,
        ];

        $out = [];

        foreach ($candidates as $candidate) {
            if ($candidate === null) {
                continue;
            }

            if (\is_string($candidate)) {
                $parts = preg_split('/\s+/', trim($candidate)) ?: [];
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '') {
                        $out[] = $p;
                    }
                }
                continue;
            }

            if (\is_array($candidate)) {
                foreach ($candidate as $value) {
                    $value = trim((string)$value);
                    if ($value === '') {
                        continue;
                    }

                    $parts = preg_split('/\s+/', $value) ?: [];
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            $out[] = $p;
                        }
                    }
                }
            }
        }

        $out = array_values(array_unique($out));

        return $out === [] ? null : $out;
    }

    /**
     * @param array<int, mixed> $values
     * @param bool              $splitWhitespaceInItems If true, split each item by whitespace too
     *
     * @return string[]
     */
    private function normalizeStringArray(array $values, bool $splitWhitespaceInItems): array
    {
        $out = [];

        foreach ($values as $value) {
            $value = trim((string)$value);
            if ($value === '') {
                continue;
            }

            if ($splitWhitespaceInItems) {
                $parts = preg_split('/\s+/', $value) ?: [];
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '') {
                        $out[] = $p;
                    }
                }
                continue;
            }

            $out[] = $value;
        }

        return array_values(array_unique($out));
    }

    /**
     * @return array<string, Key>
     */
    private function getJwksKeys(): array
    {
        if ($this->keys !== null) {
            return $this->keys;
        }

        $cacheKey = \sprintf('jwks_%s', md5($this->jwkSetUrl));

        if ($this->cache !== null) {
            $cachedItem = $this->cache->get($cacheKey);

            if ($cachedItem !== null) {
                $jwks = $cachedItem->getValue();

                $this->keys = JWK::parseKeySet($jwks);

                return $this->keys;
            }
        }

        $jwksJson = $this->httpGet($this->jwkSetUrl);
        $jwks = json_decode($jwksJson, true);

        if (!\is_array($jwks) || !isset($jwks['keys']) || !\is_array($jwks['keys'])) {
            throw new \RuntimeException(\sprintf('Invalid JWKS response from %s', $this->jwkSetUrl));
        }

        $this->keys = JWK::parseKeySet($jwks);

        if ($this->cache !== null) {
            $this->cache->set(new CacheItem($cacheKey, $jwks, 3600));
        }

        return $this->keys;
    }

    private function httpGet(string $url): string
    {
        $context = stream_context_create(
            [
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true,
                ],
            ]
        );

        $body = file_get_contents($url, false, $context);

        if ($body === false || $body === '') {
            throw new \RuntimeException(\sprintf('Could not fetch JWKS from %s', $url));
        }

        return $body;
    }
}
