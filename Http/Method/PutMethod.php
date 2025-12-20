<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Method;

class PutMethod implements MethodInterface
{
    public function getName(): string
    {
        return self::METHOD_PUT;
    }
}
