<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PHPUnit\Http\i18n;

use apivalk\apivalk\Http\i18n\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    /**
     * @dataProvider dataProviderLocaleFactory
     */
    public function testStaticFactoryCreatesExpectedLocale(
        string $method,
        string $expectedTag,
        string $expectedLanguage,
        ?string $expectedRegion
    ): void {
        $locale = Locale::$method();

        self::assertInstanceOf(Locale::class, $locale);

        self::assertSame($expectedTag, $locale->getTag());
        self::assertSame($expectedLanguage, $locale->getLanguage());
        self::assertSame($expectedRegion, $locale->getRegion());
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: ?string}>
     */
    public static function dataProviderLocaleFactory(): array
    {
        return [
            'de' => ['de', 'de', 'de', null],
            'en' => ['en', 'en', 'en', null],
            'fr' => ['fr', 'fr', 'fr', null],

            'deDe' => ['deDe', 'de-DE', 'de', 'DE'],
            'deAt' => ['deAt', 'de-AT', 'de', 'AT'],
            'deCh' => ['deCh', 'de-CH', 'de', 'CH'],

            'enUs' => ['enUs', 'en-US', 'en', 'US'],
            'enGb' => ['enGb', 'en-GB', 'en', 'GB'],
        ];
    }
}
