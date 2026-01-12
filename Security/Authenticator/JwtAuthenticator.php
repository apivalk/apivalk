<?php

declare(strict_types=1);

namespace apivalk\apivalk\Security\Authenticator;

use apivalk\apivalk\Security\AbstractAuthIdentity;
use apivalk\apivalk\Security\Scope;
use apivalk\apivalk\Security\UserAuthIdentity;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthenticator implements AuthenticatorInterface
{
    /** @var string */
    private $jwkSetUrl;
    /** @var string */
    private $issuer;
    /** @var string */
    private $audience;
    /** @var array<string, Key>|null */
    private $keys;

    /**
     * @param string $jwkSetUrl The URL to the JWK Set (e.g. https://example.com/.well-known/jwks.json)
     * @param string $issuer    Expected issuer (iss claim)
     * @param string $audience  Expected audience (aud claim)
     */
    public function __construct(string $jwkSetUrl, string $issuer, string $audience)
    {
        $this->jwkSetUrl = $jwkSetUrl;
        $this->issuer = $issuer;
        $this->audience = $audience;
    }

    public function authenticate(string $token): ?AbstractAuthIdentity
    {
        try {
            $keys = $this->getKeys();
            
            $payload = (array)JWT::decode($token, $keys);

            if (isset($payload['iss']) && $payload['iss'] !== $this->issuer) {
                return null;
            }

            if (isset($payload['aud']) && $payload['aud'] !== $this->audience) {
                return null;
            }

            $scopes = [];
            $scopeString = $payload['scope'] ?? $payload['scp'] ?? '';
            $scopeNames = \is_array($scopeString) ? $scopeString : explode(' ', (string)$scopeString);
            
            foreach (array_filter($scopeNames) as $scopeName) {
                $scopes[] = new Scope($scopeName);
            }

            return new UserAuthIdentity(
                (string)($payload['sub'] ?? ''),
                $scopes,
                $payload
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array<string, Key>
     */
    private function getKeys(): array
    {
        if ($this->keys === null) {
            $jwksJson = file_get_contents($this->jwkSetUrl);
            if (!$jwksJson) {
                throw new \RuntimeException('Could not fetch JWKS from ' . $this->jwkSetUrl);
            }

            $jwks = json_decode($jwksJson, true);
            $this->keys = JWK::parseKeySet($jwks);
        }

        return $this->keys;
    }
}
