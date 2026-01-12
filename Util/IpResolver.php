<?php

declare(strict_types=1);

namespace apivalk\apivalk\Util;

class IpResolver
{
    public static function getClientIp(): ?string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
            if (self::isValidPublicIp($ip)) {
                return $ip;
            }
        }

        $headerCandidates = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED',
        ];

        foreach ($headerCandidates as $header) {
            if (empty($_SERVER[$header])) {
                continue;
            }

            $ips = self::extractIps($_SERVER[$header]);

            foreach ($ips as $ip) {
                if (self::isValidPublicIp($ip)) {
                    return $ip;
                }
            }
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = trim($_SERVER['REMOTE_ADDR']);
            if (self::isValidPublicIp($ip)) {
                return $ip;
            }
        }

        return null;
    }

    /** @return string[] */
    private static function extractIps(string $value): array
    {
        if (strpos($value, 'for=') !== false) {
            preg_match_all('/for="?([^;,"\s]+)"?/i', $value, $matches);

            return $matches[1];
        }

        return array_map('trim', explode(',', $value));
    }

    private static function isValidPublicIp(string $ip): bool
    {
        return filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
    }
}
