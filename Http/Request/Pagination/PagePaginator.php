<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\Pagination;

class PagePaginator
{
    /** @var int */
    private $page;
    /** @var int */
    private $limit;

    public function __construct(int $page, int $limit)
    {
        if ($page < 0) {
            $page = 0;
        }

        if ($limit <= 0) {
            $limit = 50;
        }

        $this->page = $page;
        $this->limit = $limit;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
