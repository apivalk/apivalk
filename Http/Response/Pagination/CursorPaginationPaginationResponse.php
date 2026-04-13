<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response\Pagination;

use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;

class CursorPaginationPaginationResponse implements PaginationResponseInterface
{
    /** @var int */
    private $limit;
    /** @var string */
    private $currentCursor;
    /** @var string */
    private $nextCursor;
    /** @var bool */
    private $hasMore;

    public function __construct(
        int $limit,
        string $currentCursor,
        string $nextCursor,
        bool $hasMore
    ) {
        $this->limit = $limit;
        $this->currentCursor = $currentCursor;
        $this->nextCursor = $nextCursor;
        $this->hasMore = $hasMore;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getCurrentCursor(): string
    {
        return $this->currentCursor;
    }

    public function getNextCursor(): string
    {
        return $this->nextCursor;
    }

    public function isHasMore(): bool
    {
        return $this->hasMore;
    }

    /** @return array{limit: int, current_cursor: string, next_cursor?: string, has_more: bool} */
    public function toArray(): array
    {
        return array_filter(
            [
                'limit' => $this->limit,
                'current_cursor' => $this->currentCursor,
                'next_cursor' => $this->nextCursor,
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

        $nextCursor = new StringProperty(
            'next_cursor',
            'Opaque cursor to retrieve the next page. Omit or null if no further results are available.'
        );
        $nextCursor->setExample('eyJpZCI6MTIzfQ==');
        $nextCursor->setIsRequired(false);

        $hasMore = new BooleanProperty(
            'has_more',
            'Indicates whether more items are available after this result set.',
            true
        );
        $hasMore->setExample('true');

        $properties[] = $limit;
        $properties[] = $nextCursor;
        $properties[] = $hasMore;

        return $properties;
    }
}
