<?php

declare(strict_types=1);

namespace PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\DatetimeValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Property\StringProperty;

class DatetimeValidatorTest extends TestCase
{
    public function testValidDateFormat(): void
    {
        $property = new StringProperty('test');
        $property->setFormat(StringProperty::FORMAT_DATE);
        $validator = new DatetimeValidator($property);

        $this->assertTrue($validator->validate('2023-12-20')->isSuccess());
        $this->assertTrue($validator->validate('2024-01-01')->isSuccess());
        $this->assertTrue($validator->validate('1999-06-15')->isSuccess());
    }

    public function testInvalidDateFormat(): void
    {
        $property = new StringProperty('test');
        $property->setFormat(StringProperty::FORMAT_DATE);
        $validator = new DatetimeValidator($property);

        $result = $validator->validate('2023-13-20');
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_VALID_DATE, $result->getMessage());

        $this->assertFalse($validator->validate('20-12-2023')->isSuccess());
        $this->assertFalse($validator->validate('2023/12/20')->isSuccess());
        $this->assertFalse($validator->validate('invalid')->isSuccess());
    }

    public function testValidDateTimeFormat(): void
    {
        $property = new StringProperty('test');
        $property->setFormat(StringProperty::FORMAT_DATE_TIME);
        $validator = new DatetimeValidator($property);

        $this->assertTrue($validator->validate('2023-12-20T14:00:00Z')->isSuccess());
        $this->assertTrue($validator->validate('2023-12-20T14:00:00+00:00')->isSuccess());
        $this->assertTrue($validator->validate('2024-01-15T09:30:00+01:00')->isSuccess());
    }

    public function testInvalidDateTimeFormat(): void
    {
        $property = new StringProperty('test');
        $property->setFormat(StringProperty::FORMAT_DATE_TIME);
        $validator = new DatetimeValidator($property);

        $result = $validator->validate('2023-12-20 14:00:00');
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_VALID_DATE_TIME, $result->getMessage());

        $this->assertFalse($validator->validate('2023-12-20')->isSuccess());
        $this->assertFalse($validator->validate('invalid')->isSuccess());
    }
}
