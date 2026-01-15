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
        
        $property->setPattern(null)->setFormat(StringProperty::FORMAT_BYTE);
        $this->assertTrue($validator->validate(base64_encode('test'))->isSuccess());
        $this->assertFalse($validator->validate('not base64!')->isSuccess());
    }
}
