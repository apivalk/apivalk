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

    public function testToArrayFiltersNull(): void
    {
        $response = new OffsetPaginationResponse(20, 0, false, null);
        $result = $response->toArray();
        
        $this->assertEquals(20, $result['limit']);
        // 0 is filtered out!
        $this->assertArrayNotHasKey('offset', $result);
        $this->assertArrayNotHasKey('total', $result);
        $this->assertArrayNotHasKey('has_more', $result);
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
