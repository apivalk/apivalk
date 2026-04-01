<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\i18n;

class LocaleResolver
{
    public static function resolve(LocalizationConfiguration $localizationConfiguration): Locale
    {
        $acceptLanguageHeader = self::getAcceptLanguageHeaderFromCurrentRequest();

        if ($acceptLanguageHeader === null || trim($acceptLanguageHeader) === '') {
            return $localizationConfiguration->getDefaultLocale();
        }

        $supportedLocales = $localizationConfiguration->getSupportedLocales();

        foreach (self::parseAcceptLanguageHeader($acceptLanguageHeader) as $tag) {
            $locale = new Locale($tag);
            $matchedLocale = self::matchSupportedLocale($locale, $supportedLocales);

            if ($matchedLocale !== null) {
                return $matchedLocale;
            }
        }

        return $localizationConfiguration->getDefaultLocale();
    }

    private static function getAcceptLanguageHeaderFromCurrentRequest(): ?string
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $acceptLanguageHeader = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        if (!\is_string($acceptLanguageHeader)) {
            return null;
        }

        return $acceptLanguageHeader;
    }

    /**
     * @param array<string, Locale> $supportedLocales
     */
    private static function matchSupportedLocale(Locale $locale, array $supportedLocales): ?Locale
    {
        if (isset($supportedLocales[$locale->getTag()])) {
            return $supportedLocales[$locale->getTag()];
        }

        if (isset($supportedLocales[$locale->getLanguage()])) {
            return $supportedLocales[$locale->getLanguage()];
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private static function parseAcceptLanguageHeader(string $acceptLanguageHeader): array
    {
        $parts = explode(',', $acceptLanguageHeader);
        $weightedLocales = [];

        foreach ($parts as $index => $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $segments = explode(';', $part);
            $tag = trim($segments[0]);

            if ($tag === '' || $tag === '*') {
                continue;
            }

            $quality = 1.0;

            foreach ($segments as $segment) {
                $segment = trim($segment);

                if (strncmp($segment, 'q=', 2) === 0) {
                    $value = substr($segment, 2);

                    if (is_numeric($value)) {
                        $quality = (float)$value;
                    }
                }
            }

            $weightedLocales[] = [
                'tag' => $tag,
                'quality' => $quality,
                'index' => $index,
            ];
        }

        usort($weightedLocales, static function (array $left, array $right): int {
            if ($left['quality'] === $right['quality']) {
                return $left['index'] <=> $right['index'];
            }

            if ($left['quality'] > $right['quality']) {
                return -1;
            }

            return 1;
        });

        return array_map(static function (array $item): string {
            return $item['tag'];
        }, $weightedLocales);
    }
}
