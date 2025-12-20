<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response;

use apivalk\apivalk\Http\Request\Paginator;

class ResponsePagination
{
    /** @var int */
    private $page;
    /** @var int|null */
    private $totalPages;
    /** @var int */
    private $pageSize;

    /**
     * @param int      $page
     * @param int|null $totalPages
     * @param int      $pageSize
     */
    public function __construct(int $page, ?int $totalPages, int $pageSize)
    {
        $this->page = $page;
        $this->totalPages = $totalPages;
        $this->pageSize = $pageSize;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'total_pages' => $this->totalPages,
            'page_size' => $this->pageSize
        ];
    }

    public static function createByPaginator(Paginator $paginator): self
    {
        return new self($paginator->getPage(), $paginator->getTotalPages(), $paginator->getPageSize());
    }
}
