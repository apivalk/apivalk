<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Response\Pagination;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Response\Pagination\OffsetPaginationResponse;

class OffsetPaginationResponseTest extends TestCase
{
    public function testToArray(): void
    {
        $response = new OffsetPaginationResponse(20, 40, true, 100);
        $expected = [
            'limit' => 20,
            'offset' => 40,
            'total' => 100,
            'has_more' => true,
        ];
        $this->assertEquals($expected, $response->toArray());
    }

    public function testToArrayOnFirstPageKeepsZeroOffsetAndHasMoreFalse(): void
    {
        $response = new OffsetPaginationResponse(20, 0, false, null);

        $this->assertSame(
            ['limit' => 20, 'offset' => 0, 'has_more' => false],
            $response->toArray()
        );
    }

    public function testToArrayKeepsZeroTotal(): void
    {
        $response = new OffsetPaginationResponse(20, 0, false, 0);

        $this->assertSame(0, $response->toArray()['total']);
    }

    public function testGetResponseDocumentationProperties(): void
    {
        $properties = OffsetPaginationResponse::getResponseDocumentationProperties();
        $this->assertCount(4, $properties);
        $this->assertEquals('limit', $properties[0]->getPropertyName());
        $this->assertEquals('offset', $properties[1]->getPropertyName());
        $this->assertEquals('total_items', $properties[2]->getPropertyName());
        $this->assertEquals('has_more', $properties[3]->getPropertyName());
    }
}
