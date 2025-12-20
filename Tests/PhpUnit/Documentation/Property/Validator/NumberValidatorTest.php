<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\NumberValidator;
use apivalk\apivalk\Documentation\Property\NumberProperty;

class NumberValidatorTest extends TestCase
{
    public function testNumberValidator()
    {
        $property = new NumberProperty('test');
        $validator = new NumberValidator($property);

        $this->assertTrue($validator->validate(123)->isSuccess());
        $this->assertTrue($validator->validate('123.45')->isSuccess());
        $this->assertFalse($validator->validate('abc')->isSuccess());

        $property->setMinimumValue(10);
        $this->assertTrue($validator->validate(10)->isSuccess());
        $this->assertTrue($validator->validate(11)->isSuccess());
        $this->assertFalse($validator->validate(9)->isSuccess());

        $property->setIsExclusiveMinimum(true);
        $this->assertFalse($validator->validate(10)->isSuccess());
        $this->assertTrue($validator->validate(10.1)->isSuccess());

        $property->setMinimumValue(null)->setIsExclusiveMinimum(false)->setMaximumValue(20);
        $this->assertTrue($validator->validate(20)->isSuccess());
        $this->assertTrue($validator->validate(19)->isSuccess());
        $this->assertFalse($validator->validate(21)->isSuccess());

        $property->setIsExclusiveMaximum(true);
        $this->assertFalse($validator->validate(20)->isSuccess());
        $this->assertTrue($validator->validate(19.9)->isSuccess());
    }
}
