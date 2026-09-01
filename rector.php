<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Apivalk.php',
        __DIR__ . '/ApivalkConfiguration.php',
        __DIR__ . '/ApivalkExceptionHandler.php',
        __DIR__ . '/Cache',
        __DIR__ . '/Documentation',
        __DIR__ . '/Http',
        __DIR__ . '/Middleware',
        __DIR__ . '/Resource',
        __DIR__ . '/Router',
        __DIR__ . '/Security',
        __DIR__ . '/Tests',
        __DIR__ . '/Util',
    ])
    ->withPhpVersion(PhpVersion::PHP_74)
    ->withImportNames(true, false, false, false)
    ->withPhpSets()
    ->withSkip([
        // reordering constructor arguments would break every caller of a published API
        OptionalParametersAfterRequiredRector::class,
    ])
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class,
        TypedPropertyFromAssignsRector::class,
        RemoveUselessVarTagRector::class,
    ]);
