<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

interface ObjectInterface
{
    public function toArray(): array;
}
