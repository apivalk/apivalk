<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PHPUnit\Http\i18n;

use apivalk\apivalk\Http\i18n\Locale;
use apivalk\apivalk\Http\i18n\LocaleResolver;
use apivalk\apivalk\Http\i18n\LocalizationConfiguration;
use PHPUnit\Framework\TestCase;

class LocaleResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);

        parent::tearDown();
    }

    /**
     * @dataProvider dataProviderResolve
     */
    public function testResolve(?string $acceptLanguageHeader, string $expectedTag): void
    {
        if ($acceptLanguageHeader !== null) {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $acceptLanguageHeader;
        } else {
            unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        $configuration = new LocalizationConfiguration(Locale::en());
        $configuration->addSupportedLocale(Locale::de());
        $configuration->addSupportedLocale(Locale::deAt());
        $configuration->addSupportedLocale(Locale::en());

        $locale = LocaleResolver::resolve($configuration);

        self::assertInstanceOf(Locale::class, $locale);
        self::assertSame($expectedTag, $locale->getTag());
    }

    /**
     * @return array<string, array{0: ?string, 1: string}>
     */
    public static function dataProviderResolve(): array
    {
        return [
            'missing header returns default locale' => [null, 'en'],
            'empty header returns default locale' => ['', 'en'],
            'exact locale match' => ['de-AT,de;q=0.9,en;q=0.8', 'de-AT'],
            'lowercase locale match' => ['de-at,de;q=0.9,en;q=0.8', 'de-AT'],
            'underscore locale match' => ['de_AT,de;q=0.9,en;q=0.8', 'de-AT'],
            'language fallback match' => ['de-DE,de;q=0.9,en;q=0.8', 'de'],
            'default locale fallback' => ['fr-FR,fr;q=0.9,en;q=0.8', 'en'],
            'higher quality wins' => ['en;q=0.8,de;q=0.9', 'de'],
            'same quality keeps order' => ['en,de', 'en'],
        ];
    }
}
