<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Pagination;

class Pagination
{
    public const TYPE_CURSOR = 'cursor';
    public const TYPE_OFFSET = 'offset';
    public const TYPE_PAGE = 'page';

    /** @var int */
    private $maxLimit = 100;
    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public static function cursor(): self
    {
        return new self(self::TYPE_CURSOR);
    }

    public static function offset(): self
    {
        return new self(self::TYPE_OFFSET);
    }

    public static function page(): self
    {
        return new self(self::TYPE_PAGE);
    }

    public function setMaxLimit(int $maxLimit): self
    {
        $this->maxLimit = $maxLimit;

        return $this;
    }

    public function getMaxLimit(): int
    {
        return $this->maxLimit;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
