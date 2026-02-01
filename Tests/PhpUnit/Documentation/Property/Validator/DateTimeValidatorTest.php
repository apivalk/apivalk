<?php

declare(strict_types=1);

namespace PhpUnit\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\Validator\DateTimeValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class DateTimeValidatorTest extends TestCase
{
    public function testValidDateFormats(): void
    {
        $property = new StringProperty('test');
        $property->setFormat(StringProperty::FORMAT_DATE);
        $validator = new DateTimeValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20', '2023-12-20'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-01-01', '2023-01-01'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2024-02-29', '2024-02-29'))->isSuccess()); // leap year
        $this->assertTrue($validator->validate(new Parameter('test', '1999-12-31', '1999-12-31'))->isSuccess());
    }

    public function testInvalidDateFormats(): void
    {
        $property = new StringProperty('test');
        $property->setFormat(StringProperty::FORMAT_DATE);
        $validator = new DateTimeValidator($property);

        $result = $validator->validate(new Parameter('test', '2023-13-20', '2023-13-20'));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_VALID_DATE, $result->getErrorKey());

        $this->assertFalse($validator->validate(new Parameter('test', '20-12-2023', '20-12-2023'))->isSuccess()); // wrong format
        $this->assertFalse($validator->validate(new Parameter('test', '2023/12/20', '2023/12/20'))->isSuccess()); // wrong separator
        $this->assertFalse($validator->validate(new Parameter('test', '2023-00-20', '2023-00-20'))->isSuccess()); // month 0
        $this->assertFalse($validator->validate(new Parameter('test', '2023-12-32', '2023-12-32'))->isSuccess()); // day 32
        $this->assertFalse($validator->validate(new Parameter('test', '2023-02-29', '2023-02-29'))->isSuccess()); // not leap year
        $this->assertFalse($validator->validate(new Parameter('test', 'not-a-date', 'not-a-date'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', '', ''))->isSuccess());
    }

    public function testValidDateTimeFormats(): void
    {
        $property = new StringProperty('test');
        $property->setFormat(StringProperty::FORMAT_DATE_TIME);
        $validator = new DateTimeValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T14:00:00Z', '2023-12-20T14:00:00Z'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T14:00:00+00:00', '2023-12-20T14:00:00+00:00'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T14:00:00+02:00', '2023-12-20T14:00:00+02:00'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T00:00:00Z', '2023-12-20T00:00:00Z'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T23:59:59Z', '2023-12-20T23:59:59Z'))->isSuccess());
    }

    public function testInvalidDateTimeFormats(): void
    {
        $property = new StringProperty('test');
        $property->setFormat(StringProperty::FORMAT_DATE_TIME);
        $validator = new DateTimeValidator($property);

        $result = $validator->validate(new Parameter('test', '2023-12-20 14:00:00', '2023-12-20 14:00:00'));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_VALID_DATE_TIME, $result->getErrorKey());

        $this->assertFalse($validator->validate(new Parameter('test', '2023-12-20', '2023-12-20'))->isSuccess()); // date only
        $this->assertFalse($validator->validate(new Parameter('test', '14:00:00', '14:00:00'))->isSuccess()); // time only
        $this->assertFalse($validator->validate(new Parameter('test', '2023-12-20T25:00:00Z', '2023-12-20T25:00:00Z'))->isSuccess()); // invalid hour
        $this->assertFalse($validator->validate(new Parameter('test', '2023-12-20T14:60:00Z', '2023-12-20T14:60:00Z'))->isSuccess()); // invalid minute
        $this->assertFalse($validator->validate(new Parameter('test', 'not-a-datetime', 'not-a-datetime'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', '', ''))->isSuccess());
    }

    public function testNoFormatReturnsSuccess(): void
    {
        $property = new StringProperty('test');
        $validator = new DateTimeValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 'any-string', 'any-string'))->isSuccess());
    }
}
