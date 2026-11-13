<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\i18n;

interface ValidationMessageTranslatorInterface
{
    /**
     * @param string $message the English message apivalk sends without a translator
     */
    public function translate(string $errorKey, string $message, Locale $locale): string;
}
