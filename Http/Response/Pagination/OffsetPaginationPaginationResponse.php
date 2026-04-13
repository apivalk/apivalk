<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response\Pagination;

use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;

class OffsetPaginationPaginationResponse implements PaginationResponseInterface
{
    /** @var int */
    private $limit;
    /** @var int */
    private $offset;
    /** @var int|null */
    private $total;
    /** @var bool */
    private $hasMore;

    public function __construct(int $limit, int $offset, bool $hasMore, ?int $total = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        $this->hasMore = $hasMore;
        $this->total = $total;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function isHasMore(): bool
    {
        return $this->hasMore;
    }

    /** @return array{limit: int, offset: int, total?: int, has_more: bool} */
    public function toArray(): array
    {
        return array_filter(
            [
                'limit' => $this->limit,
                'offset' => $this->offset,
                'total' => $this->total,
                'has_more' => $this->hasMore,
            ]
        );
    }

    public static function getResponseDocumentationProperties(): array
    {
        $properties = [];

        $limit = new IntegerProperty(
            'limit',
            'Maximum number of items returned in this response.',
            IntegerProperty::FORMAT_INT64
        );
        $limit->setExample('10');

        $offset = new IntegerProperty(
            'offset',
            'Number of items skipped before this result set.',
            IntegerProperty::FORMAT_INT64
        );
        $offset->setExample('0');
        $offset->setMinimumValue(0);

        $totalItems = new IntegerProperty(
            'total_items',
            'Total number of items available.',
            IntegerProperty::FORMAT_INT64
        );
        $totalItems->setExample('57');

        $hasMore = new BooleanProperty(
            'has_more',
            'Indicates whether more items are available after this result set.',
            true
        );
        $hasMore->setExample('true');

        $properties[] = $limit;
        $properties[] = $offset;
        $properties[] = $totalItems;
        $properties[] = $hasMore;

        return $properties;
    }
}
