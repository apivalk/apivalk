<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\NumberValidator;
use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class NumberValidatorTest extends TestCase
{
    public function testNumberValidator()
    {
        $property = new NumberProperty('test');
        $validator = new NumberValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 123, 123))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '123.45', '123.45'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 'abc', 'abc'))->isSuccess());

        $property->setMinimumValue(10);
        $this->assertTrue($validator->validate(new Parameter('test', 10, 10))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 11, 11))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 9, 9))->isSuccess());

        $property->setIsExclusiveMinimum(true);
        $this->assertFalse($validator->validate(new Parameter('test', 10, 10))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 10.1, 10.1))->isSuccess());

        $property->setMinimumValue(null)->setIsExclusiveMinimum(false)->setMaximumValue(20);
        $this->assertTrue($validator->validate(new Parameter('test', 20, 20))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 19, 19))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 21, 21))->isSuccess());

        $property->setIsExclusiveMaximum(true);
        $this->assertFalse($validator->validate(new Parameter('test', 20, 20))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 19.9, 19.9))->isSuccess());
    }
}
