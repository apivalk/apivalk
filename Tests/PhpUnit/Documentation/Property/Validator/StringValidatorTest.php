<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\StringValidator;
use apivalk\apivalk\Documentation\Property\StringProperty;

class StringValidatorTest extends TestCase
{
    public function testStringValidator()
    {
        $property = new StringProperty('test');
        $validator = new StringValidator($property);

        $this->assertTrue($validator->validate('hello')->isSuccess());
        $this->assertFalse($validator->validate(123)->isSuccess());

        $property->setEnums(['a', 'b']);
        $this->assertTrue($validator->validate('a')->isSuccess());
        $this->assertFalse($validator->validate('c')->isSuccess());

        $property->setEnums([])->setMinLength(5);
        $this->assertTrue($validator->validate('12345')->isSuccess());
        $this->assertFalse($validator->validate('1234')->isSuccess());

        $property->setMinLength(null)->setMaxLength(5);
        $this->assertTrue($validator->validate('12345')->isSuccess());
        $this->assertFalse($validator->validate('123456')->isSuccess());

        $property->setMaxLength(null)->setPattern('/^abc/');
        $this->assertTrue($validator->validate('abcdef')->isSuccess());
        $this->assertFalse($validator->validate('bcdef')->isSuccess());

        $property->setPattern(null)->setFormat(StringProperty::FORMAT_DATE);
        $this->assertTrue($validator->validate('2023-12-20')->isSuccess());
        $this->assertFalse($validator->validate('2023-13-20')->isSuccess());
        $this->assertFalse($validator->validate('20-12-2023')->isSuccess());

        $property->setFormat(StringProperty::FORMAT_DATE_TIME);
        $this->assertTrue($validator->validate('2023-12-20T14:00:00Z')->isSuccess());
        $this->assertFalse($validator->validate('2023-12-20 14:00:00')->isSuccess());
        
        $property->setFormat(StringProperty::FORMAT_BYTE);
        $this->assertTrue($validator->validate(base64_encode('test'))->isSuccess());
        $this->assertFalse($validator->validate('not base64!')->isSuccess());
    }
}
