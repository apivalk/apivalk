<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Pagination;

class OffsetPaginator
{
    /** @var int */
    private $offset;
    /** @var int */
    private $limit;

    public function __construct(int $offset, int $limit)
    {
        if ($offset < 0) {
            $offset = 0;
        }

        if ($limit <= 0) {
            $limit = 50;
        }

        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
