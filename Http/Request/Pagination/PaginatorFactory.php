<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Pagination;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;

class PaginatorFactory
{
    public static function offset(AbstractApivalkRequest $apivalkRequest, int $maxLimit = 100): OffsetPaginator
    {
        $limit = self::resolveLimit($apivalkRequest, $maxLimit);
        $offset = 0;

        if ($apivalkRequest->query()->has('offset')) {
            $offset = max(0, (int)$apivalkRequest->query()->get('offset')->getValue());
        }

        return new OffsetPaginator($offset, $limit);
    }

    public static function cursor(AbstractApivalkRequest $apivalkRequest, int $maxLimit = 100): CursorPaginator
    {
        $limit = self::resolveLimit($apivalkRequest, $maxLimit);

        $cursor = null;

        if ($apivalkRequest->query()->has('cursor')) {
            $cursor = (string)$apivalkRequest->query()->get('cursor')->getValue();

            if ($cursor === '') {
                $cursor = null;
            }
        }

        return new CursorPaginator($cursor, $limit);
    }

    public static function page(AbstractApivalkRequest $apivalkRequest, int $maxLimit = 100): PagePaginator
    {
        $limit = self::resolveLimit($apivalkRequest, $maxLimit);
        $page = 1;

        if ($apivalkRequest->query()->has('page')) {
            $page = max(1, (int)$apivalkRequest->query()->get('page')->getValue());
        }

        return new PagePaginator($page, $limit);
    }

    private static function resolveLimit(AbstractApivalkRequest $apivalkRequest, int $maxLimit): int
    {
        $maxLimit = max(1, $maxLimit);
        $limit = min(50, $maxLimit);

        if ($apivalkRequest->query()->has('limit')) {
            $limit = min($maxLimit, max(1, (int)$apivalkRequest->query()->get('limit')->getValue()));
        }

        return $limit;
    }
}
