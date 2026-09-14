<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Response\Stub;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class StubSuccessResponse extends AbstractApivalkResponse
{
    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        return new ApivalkResponseDocumentation();
    }

    public static function getStatusCode(): int
    {
        return 200;
    }

    public function toArray(): array
    {
        return [];
    }
}
