<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationResponse;

class PagePaginationResponseTest extends TestCase
{
    public function testToArray(): void
    {
        $response = new PagePaginationResponse(1, 10, true, 5);
        $expected = [
            'page' => 1,
            'total_pages' => 5,
            'page_size' => 10,
            'has_more' => true,
        ];
        $this->assertEquals($expected, $response->toArray());
    }

    public function testToArrayOnLastPageOmitsNullTotalPagesAndKeepsHasMoreFalse(): void
    {
        $response = new PagePaginationResponse(1, 10, false, null);

        $this->assertSame(
            ['page' => 1, 'page_size' => 10, 'has_more' => false],
            $response->toArray()
        );
    }

    public function testToArrayKeepsZeroTotalPages(): void
    {
        $response = new PagePaginationResponse(1, 10, false, 0);

        $this->assertSame(0, $response->toArray()['total_pages']);
    }

    public function testGetResponseDocumentationProperties(): void
    {
        $properties = PagePaginationResponse::getResponseDocumentationProperties();
        $this->assertCount(4, $properties);
        $this->assertEquals('page', $properties[0]->getPropertyName());
        $this->assertEquals('total_pages', $properties[1]->getPropertyName());
        $this->assertEquals('page_size', $properties[2]->getPropertyName());
        $this->assertEquals('has_more', $properties[3]->getPropertyName());
    }
}
