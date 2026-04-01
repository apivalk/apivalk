<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\i18n;

class LocalizationConfiguration
{
    /** @var Locale */
    private $defaultLocale;
    /** @var array<string, Locale> */
    private $supportedLocales = [];

    public function __construct(Locale $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function addSupportedLocale(Locale $locale): void
    {
        $this->supportedLocales[$locale->getTag()] = $locale;
    }

    public function getDefaultLocale(): Locale
    {
        return $this->defaultLocale;
    }

    /**
     * @return array<string, Locale>
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }
}
