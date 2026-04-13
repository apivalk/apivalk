<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Filter;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\FilterBag;

class FilterBagTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $bag = new FilterBag();
        $filter = StringFilter::equals(new StringProperty('status'));

        $bag->set($filter);

        $this->assertTrue($bag->has('status'));
        $this->assertSame($filter, $bag->get('status'));
        $this->assertSame($filter, $bag->status);
    }

    public function testIteration(): void
    {
        $bag = new FilterBag();
        $bag->set(StringFilter::equals(new StringProperty('status')));
        $bag->set(StringFilter::in(new StringProperty('type')));

        $this->assertCount(2, $bag);

        foreach ($bag as $field => $filter) {
            $this->assertInstanceOf(FilterInterface::class, $filter);
            $this->assertContains($field, ['status', 'type']);
        }
    }

    public function testNonExistentFilter(): void
    {
        $bag = new FilterBag();
        $this->assertFalse($bag->has('missing'));
        $this->assertNull($bag->get('missing'));
        $this->assertNull($bag->missing);
    }
}
