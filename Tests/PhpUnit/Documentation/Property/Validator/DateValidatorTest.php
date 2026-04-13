<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Documentation\Property\Validator\DateValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class DateValidatorTest extends TestCase
{
    public function testValidDateFormats(): void
    {
        $property = new DateProperty('test');
        $validator = new DateValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', '2023-12-20', '2023-12-20'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2023-01-01', '2023-01-01'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '2024-02-29', '2024-02-29'))->isSuccess()); // leap year
        $this->assertTrue($validator->validate(new Parameter('test', '1999-12-31', '1999-12-31'))->isSuccess());
    }

    public function testInvalidDateFormats(): void
    {
        $property = new DateProperty('test');
        $validator = new DateValidator($property);

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
}
