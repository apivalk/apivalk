<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\FloatValidator;
use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class FloatValidatorTest extends TestCase
{
    public function testFloatValidator(): void
    {
        $property = new FloatProperty('test');
        $validator = new FloatValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 123.45, 123.45))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '123.45', '123.45'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 'abc', 'abc'))->isSuccess());

        $property->setMinimumValue(10);
        $this->assertTrue($validator->validate(new Parameter('test', 10.0, 10.0))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 10.1, 10.1))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 9.9, 9.9))->isSuccess());

        $property->setIsExclusiveMinimum(true);
        $this->assertFalse($validator->validate(new Parameter('test', 10.0, 10.0))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 10.1, 10.1))->isSuccess());

        $property->setMinimumValue(null)->setIsExclusiveMinimum(false)->setMaximumValue(20);
        $this->assertTrue($validator->validate(new Parameter('test', 20.0, 20.0))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 19.9, 19.9))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 20.1, 20.1))->isSuccess());

        $property->setIsExclusiveMaximum(true);
        $this->assertFalse($validator->validate(new Parameter('test', 20.0, 20.0))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 19.9, 19.9))->isSuccess());
    }
}
