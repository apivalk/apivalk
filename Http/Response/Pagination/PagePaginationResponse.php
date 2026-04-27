<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response\Pagination;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;

class PagePaginationResponse implements PaginationResponseInterface
{
    /** @var int */
    private $page;
    /** @var int|null */
    private $totalPages;
    /** @var int */
    private $pageSize;
    /** @var bool */
    private $hasMore;

    public function __construct(int $page, int $pageSize, bool $hasMore, ?int $totalPages = null)
    {
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->hasMore = $hasMore;
        $this->totalPages = $totalPages;
    }

    /** @return array{ page: int, total_pages: int|null, page_size: int, has_more: bool} */
    public function toArray(): array
    {
        return array_filter(
            [
                'page' => $this->page,
                'total_pages' => $this->totalPages,
                'page_size' => $this->pageSize,
                'has_more' => $this->hasMore,
            ]
        );
    }

    /** @return AbstractProperty[] */
    public static function getResponseDocumentationProperties(): array
    {
        $properties = [];

        $pageProperty = new IntegerProperty(
            'page',
            'Current page number (starting from 1).',
            IntegerProperty::FORMAT_INT64
        );
        $pageProperty->setExample('1');

        $totalPagesProperty = new IntegerProperty(
            'total_pages',
            'Total number of available pages.',
            IntegerProperty::FORMAT_INT64
        );
        $totalPagesProperty->setExample('5');

        $pageSizeProperty = new IntegerProperty(
            'page_size',
            'Number of items returned per page.',
            IntegerProperty::FORMAT_INT64
        );
        $pageSizeProperty->setExample('10');

        $hasMoreProperty = new BooleanProperty(
            'has_more',
            'Indicates whether more pages are available after the current page.',
            true
        );
        $hasMoreProperty->setExample('true');

        $properties[] = $pageProperty;
        $properties[] = $totalPagesProperty;
        $properties[] = $pageSizeProperty;
        $properties[] = $hasMoreProperty;

        return $properties;
    }
}
