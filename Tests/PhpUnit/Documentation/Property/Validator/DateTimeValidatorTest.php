<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Documentation\Property\Validator\DateTimeValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class DateTimeValidatorTest extends TestCase
{
    public function testValidDateTimeFormats(): void
    {
        $property = new DateTimeProperty('test');
        $validator = new DateTimeValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T14:00:00Z', '2023-12-20T14:00:00Z'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T14:00:00+00:00', '2023-12-20T14:00:00+00:00'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T14:00:00+02:00', '2023-12-20T14:00:00+02:00'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T00:00:00Z', '2023-12-20T00:00:00Z'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20T23:59:59Z', '2023-12-20T23:59:59Z'))->isSuccess());
    }

    public function testInvalidDateTimeFormats(): void
    {
        $property = new DateTimeProperty('test');
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
}
