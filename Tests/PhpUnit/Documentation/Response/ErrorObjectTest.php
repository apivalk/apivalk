<?php

declare(strict_types=1);

namespace Documentation\Response;

use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;
use apivalk\apivalk\Documentation\Response\ErrorObject;
use apivalk\apivalk\Documentation\Response\ErrorObjectPropertyCollection;
use PHPUnit\Framework\TestCase;

final class ErrorObjectTest extends TestCase
{
    public function testPopulateAndGetters(): void
    {
        $obj = new ErrorObject();
        $obj->populate('validation_failed', 'Name is required.');

        $this->assertSame('validation_failed', $obj->getErrorKey());
        $this->assertSame('Name is required.', $obj->getMessage());
    }

    public function testToArray(): void
    {
        $obj = new ErrorObject();
        $obj->populate('unauthorized', 'Missing token.');

        $this->assertSame(
            [
                'key' => 'unauthorized',
                'message' => 'Missing token.',
            ],
            $obj->toArray()
        );
    }

    public function testGetPropertyCollectionReturnsExpectedCollection(): void
    {
        $obj = new ErrorObject();
        $collection = $obj->getPropertyCollection();

        $this->assertInstanceOf(AbstractPropertyCollection::class, $collection);
        $this->assertInstanceOf(ErrorObjectPropertyCollection::class, $collection);

        if (method_exists($collection, 'getMode')) {
            $this->assertSame(AbstractPropertyCollection::MODE_VIEW, $collection->getMode());
        }
    }
}
