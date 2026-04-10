<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response\Pagination;

use apivalk\apivalk\Documentation\Property\AbstractProperty;

interface PaginationResponseInterface
{
    /** @return array<mixed, mixed> */
    public function toArray(): array;

    /** @return AbstractProperty[] */
    public static function getResponseDocumentationProperties(): array;
}
