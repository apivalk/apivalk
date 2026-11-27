<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\Pagination\CursorPaginationResponse;

class CursorPaginationResponseTest extends TestCase
{
    public function testToArray(): void
    {
        $response = new CursorPaginationResponse(15, 'cur', 'nxt', true);
        $expected = [
            'limit' => 15,
            'current_cursor' => 'cur',
            'next_cursor' => 'nxt',
            'has_more' => true,
        ];
        $this->assertEquals($expected, $response->toArray());
    }

    public function testToArrayOnFirstPageOmitsNullCurrentCursor(): void
    {
        $response = new CursorPaginationResponse(15, null, 'nxt', true);

        $this->assertSame(
            ['limit' => 15, 'next_cursor' => 'nxt', 'has_more' => true],
            $response->toArray()
        );
        $this->assertNull($response->getCurrentCursor());
    }

    public function testToArrayOnLastPageKeepsHasMoreFalse(): void
    {
        $response = new CursorPaginationResponse(15, 'cur', null, false);

        $this->assertSame(
            ['limit' => 15, 'current_cursor' => 'cur', 'has_more' => false],
            $response->toArray()
        );
        $this->assertNull($response->getNextCursor());
    }

    public function testToArrayKeepsEmptyStringCursors(): void
    {
        $response = new CursorPaginationResponse(15, '', '', false);

        $this->assertSame(
            ['limit' => 15, 'current_cursor' => '', 'next_cursor' => '', 'has_more' => false],
            $response->toArray()
        );
    }

    public function testGetResponseDocumentationProperties(): void
    {
        $properties = CursorPaginationResponse::getResponseDocumentationProperties();
        $this->assertCount(4, $properties);
        $this->assertEquals('limit', $properties[0]->getPropertyName());
        $this->assertEquals('current_cursor', $properties[1]->getPropertyName());
        $this->assertFalse($properties[1]->isRequired());
        $this->assertEquals('next_cursor', $properties[2]->getPropertyName());
        $this->assertFalse($properties[2]->isRequired());
        $this->assertEquals('has_more', $properties[3]->getPropertyName());
    }
}
