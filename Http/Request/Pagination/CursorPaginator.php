<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Pagination;

class CursorPaginator
{
    /** @var null|string */
    private $cursor;
    /** @var int */
    private $limit;

    public function __construct(?string $cursor, int $limit)
    {
        if ($limit <= 0) {
            $limit = 50;
        }

        $this->cursor = $cursor;
        $this->limit = $limit;
    }

    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
