<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\i18n;

use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;

interface ValidationMessageTranslatorInterface
{
    /**
     * The error key is a `ValidatorResult` constant, or free text for an unknown sort field.
     * `getLocalizedErrorMessage()` returns the English message apivalk sends without a translator.
     */
    public function translate(ValidatorResult $validatorResult, Locale $locale): string;
}
