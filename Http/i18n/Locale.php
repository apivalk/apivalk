<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\i18n;

/**
 * Locale value object (BCP 47)
 *
 * - language: lowercase (de) - ISO 639-1
 * - region: uppercase (DE) - ISO 3166-1
 */
class Locale
{
    /** @var string */
    private $tag;

    public static function de(): self
    {
        return new self('de');
    }

    public static function en(): self
    {
        return new self('en');
    }

    public static function fr(): self
    {
        return new self('fr');
    }

    public static function deDe(): self
    {
        return new self('de-DE');
    }

    public static function deAt(): self
    {
        return new self('de-AT');
    }

    public static function deCh(): self
    {
        return new self('de-CH');
    }

    public static function enUs(): self
    {
        return new self('en-US');
    }

    public static function enGb(): self
    {
        return new self('en-GB');
    }

    public function __construct(string $tag)
    {
        $this->tag = self::normalize($tag);
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * ISO 639-1
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return explode('-', $this->tag)[0];
    }

    /**
     * ISO 3166-1
     *
     * @return string|null
     */
    public function getRegion(): ?string
    {
        $parts = explode('-', $this->tag);

        return $parts[1] ?? null;
    }

    private static function normalize(string $tag): string
    {
        $tag = str_replace('_', '-', trim($tag));
        $parts = explode('-', $tag);

        if (isset($parts[0])) {
            $parts[0] = strtolower($parts[0]);
        }

        if (isset($parts[1])) {
            $parts[1] = strtoupper($parts[1]);
        }

        return implode('-', $parts);
    }
}
