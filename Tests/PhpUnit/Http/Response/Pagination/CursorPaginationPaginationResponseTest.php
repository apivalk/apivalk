<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\Pagination\CursorPaginationPaginationResponse;

class CursorPaginationPaginationResponseTest extends TestCase
{
    public function testToArray(): void
    {
        $response = new CursorPaginationPaginationResponse(15, 'cur', 'nxt', true);
        $expected = [
            'limit' => 15,
            'current_cursor' => 'cur',
            'next_cursor' => 'nxt',
            'has_more' => true,
        ];
        $this->assertEquals($expected, $response->toArray());
    }

    public function testToArrayFiltersEmptyStrings(): void
    {
        // currentCursor is required in constructor, but if we pass empty string it might be filtered
        $response = new CursorPaginationPaginationResponse(15, '', '', false);
        $result = $response->toArray();
        
        $this->assertEquals(15, $result['limit']);
        $this->assertArrayNotHasKey('current_cursor', $result);
        $this->assertArrayNotHasKey('next_cursor', $result);
        $this->assertArrayNotHasKey('has_more', $result);
    }

    public function testGetResponseDocumentationProperties(): void
    {
        $properties = CursorPaginationPaginationResponse::getResponseDocumentationProperties();
        $this->assertCount(3, $properties);
        $this->assertEquals('limit', $properties[0]->getPropertyName());
        $this->assertEquals('next_cursor', $properties[1]->getPropertyName());
        $this->assertEquals('has_more', $properties[2]->getPropertyName());
    }
}
