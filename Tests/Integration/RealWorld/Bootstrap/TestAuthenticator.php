<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Bootstrap;

use apivalk\apivalk\Security\AuthIdentity\AbstractAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\GuestAuthIdentity;
use apivalk\apivalk\Security\AuthIdentity\JwtAuthIdentity;
use apivalk\apivalk\Security\Authenticator\AuthenticatorInterface;

class TestAuthenticator implements AuthenticatorInterface
{
    private const ALL_SCOPES = [
        'api:customers',
        'api:customers:address',
        'api:contracts',
        'api:contracts:invoices',
    ];

    private const ALL_PERMISSIONS = [
        'api:customers:read',
        'api:customers:create',
        'api:customers:update',
        'api:customers:delete',
        'api:customers:address:read',
        'api:customers:address:create',
        'api:customers:address:update',
        'api:customers:address:delete',
        'api:contracts:read',
        'api:contracts:create',
        'api:contracts:update',
        'api:contracts:delete',
        'api:contracts:invoices:read',
        'api:contracts:invoices:create',
        'api:contracts:invoices:update',
        'api:contracts:invoices:delete',
    ];

    public function authenticate(string $token): ?AbstractAuthIdentity
    {
        switch ($token) {
            case 'admin-token':
                return new JwtAuthIdentity(null, null, null, self::ALL_SCOPES, self::ALL_PERMISSIONS);

            case 'read-only-token':
                return new JwtAuthIdentity(null, null, null, self::ALL_SCOPES, [
                    'api:customers:read',
                    'api:customers:address:read',
                    'api:contracts:read',
                    'api:contracts:invoices:read',
                ]);

            case 'customer-token':
                return new JwtAuthIdentity(null, null, null, ['api:customers'], [
                    'api:customers:read',
                    'api:customers:create',
                    'api:customers:update',
                    'api:customers:delete',
                ]);

            case 'contract-token':
                return new JwtAuthIdentity(null, null, null, [
                    'api:contracts',
                    'api:contracts:invoices',
                ], [
                    'api:contracts:read',
                    'api:contracts:create',
                    'api:contracts:update',
                    'api:contracts:delete',
                    'api:contracts:invoices:read',
                    'api:contracts:invoices:create',
                    'api:contracts:invoices:update',
                    'api:contracts:invoices:delete',
                ]);

            case 'no-scope-token':
                return new JwtAuthIdentity(null, null, null, [], []);

            default:
                return null;
        }
    }
}
