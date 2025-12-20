<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request;

class Paginator
{
    /** @var ApivalkRequestInterface */
    private $request;
    /** @var int */
    private $pageSize;
    /** @var int|null */
    private $totalPages;

    public function __construct(ApivalkRequestInterface $request, int $pageSize, ?int $totalEntries)
    {
        if ($pageSize <= 0) {
            throw new \InvalidArgumentException('Page size must be greater than 0');
        }

        $this->request = $request;
        $this->pageSize = $pageSize;

        if ($totalEntries === null) {
            $this->totalPages = null;
        } else {
            $this->totalPages = (int)ceil($totalEntries / $pageSize);
        }
    }

    public function getOffset(): int
    {
        $page = max(1, $this->getPage());

        return ($page - 1) * $this->pageSize;
    }

    public function getPage(): int
    {
        return $this->request->query()->page ?? 1;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }
}
