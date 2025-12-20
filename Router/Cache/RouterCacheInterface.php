<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Cache;

interface RouterCacheInterface
{
    public function getRouterCacheCollection(): RouterCacheCollection;
}
