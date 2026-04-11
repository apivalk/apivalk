<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationPaginationResponse;

class PagePaginationPaginationResponseTest extends TestCase
{
    public function testToArray(): void
    {
        $response = new PagePaginationPaginationResponse(1, 10, true, 5);
        $expected = [
            'page' => 1,
            'total_pages' => 5,
            'page_size' => 10,
            'has_more' => true,
        ];
        $this->assertEquals($expected, $response->toArray());
    }

    public function testToArrayFiltersNull(): void
    {
        $response = new PagePaginationPaginationResponse(1, 10, false, null);
        $expected = [
            'page' => 1,
            'page_size' => 10,
            // has_more is false, array_filter might remove it if not careful, 
            // but in PHP false is filtered out by array_filter without callback.
            // Wait, let's check PagePaginationPaginationResponse::toArray()
        ];
        // In PagePaginationPaginationResponse.php:
        // return array_filter([
        //     'page' => $this->page,
        //     'total_pages' => $this->totalPages,
        //     'page_size' => $this->pageSize,
        //     'has_more' => $this->hasMore,
        // ]);
        // If hasMore is false, it WILL be filtered out. page=0 would also be filtered.
        
        $result = $response->toArray();
        $this->assertArrayNotHasKey('total_pages', $result);
        $this->assertArrayNotHasKey('has_more', $result);
    }

    public function testGetResponseDocumentationProperties(): void
    {
        $properties = PagePaginationPaginationResponse::getResponseDocumentationProperties();
        $this->assertCount(4, $properties);
        $this->assertEquals('page', $properties[0]->getPropertyName());
        $this->assertEquals('total_pages', $properties[1]->getPropertyName());
        $this->assertEquals('page_size', $properties[2]->getPropertyName());
        $this->assertEquals('has_more', $properties[3]->getPropertyName());
    }
}
