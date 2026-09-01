<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Method;

/**
 * Safe, idempotent read with a request body, RFC 10008.
 */
class QueryMethod implements MethodInterface
{
    public function getName(): string
    {
        return self::METHOD_QUERY;
    }
}
