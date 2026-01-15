<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Parameter;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class ParameterTest extends TestCase
{
    public function testParameter(): void
    {
        $parameter = new Parameter('id', 123, 123);
        $this->assertEquals('id', $parameter->getName());
        $this->assertEquals(123, $parameter->getValue());
        $this->assertEquals(123, $parameter->getRawValue());

        $parameter->setValue('abc');
        $this->assertEquals('abc', $parameter->getValue());
    }

    public function testParameterWithRawValue(): void
    {
        $parameter = new Parameter('id', 123, '123');
        $this->assertEquals('id', $parameter->getName());
        $this->assertEquals(123, $parameter->getValue());
        $this->assertEquals('123', $parameter->getRawValue());
    }
}
