<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PHPUnit\Http\i18n;

use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\i18n\LocalizationConfiguration;
use PHPUnit\Framework\TestCase;

final class LocalizationConfigurationTest extends TestCase
{
    public function testConstructorSetsDefaultLocale(): void
    {
        $defaultLocale = Locale::de();
        $configuration = new LocalizationConfiguration($defaultLocale);

        self::assertSame($defaultLocale, $configuration->getDefaultLocale());
        self::assertEmpty($configuration->getSupportedLocales());
    }

    public function testAddSupportedLocaleAddsLocaleUsingTagAsKey(): void
    {
        $configuration = new LocalizationConfiguration(Locale::de());
        $locale = Locale::en();

        $configuration->addSupportedLocale($locale);

        self::assertSame(
            [
                'en' => $locale,
            ],
            $configuration->getSupportedLocales()
        );
    }

    public function testAddSupportedLocaleAddsMultipleLocales(): void
    {
        $configuration = new LocalizationConfiguration(Locale::de());
        $englishLocale = Locale::en();
        $frenchLocale = Locale::fr();

        $configuration->addSupportedLocale($englishLocale);
        $configuration->addSupportedLocale($frenchLocale);

        self::assertSame(
            [
                'en' => $englishLocale,
                'fr' => $frenchLocale,
            ],
            $configuration->getSupportedLocales()
        );
    }

    public function testAddSupportedLocaleOverridesExistingLocaleWithSameTag(): void
    {
        $configuration = new LocalizationConfiguration(Locale::de());
        $firstLocale = new Locale('en');
        $secondLocale = Locale::en();

        $configuration->addSupportedLocale($firstLocale);
        $configuration->addSupportedLocale($secondLocale);

        $supportedLocales = $configuration->getSupportedLocales();

        self::assertCount(1, $supportedLocales);
        self::assertArrayHasKey('en', $supportedLocales);
        self::assertSame($secondLocale, $supportedLocales['en']);
    }
}
