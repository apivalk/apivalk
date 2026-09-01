<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Method;

interface MethodInterface
{
    /** @var string */
    public const METHOD_GET = 'GET';
    /** @var string */
    public const METHOD_POST = 'POST';
    /** @var string */
    public const METHOD_DELETE = 'DELETE';
    /** @var string */
    public const METHOD_PUT = 'PUT';
    /** @var string */
    public const METHOD_PATCH = 'PATCH';

    /** @var string */
    public const METHOD_QUERY = 'QUERY';

    public function getName(): string;
}
