<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\StringValidator;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class StringValidatorTest extends TestCase
{
    public function testStringValidator()
    {
        $property = new StringProperty('test');
        $validator = new StringValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 'hello', 'hello'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 123, 123))->isSuccess());

        $property->setEnums(['a', 'b']);
        $this->assertTrue($validator->validate(new Parameter('test', 'a', 'a'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 'c', 'c'))->isSuccess());

        $property->setEnums([])->setMinLength(5);
        $this->assertTrue($validator->validate(new Parameter('test', '12345', '12345'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', '1234', '1234'))->isSuccess());

        $property->setMinLength(null)->setMaxLength(5);
        $this->assertTrue($validator->validate(new Parameter('test', '12345', '12345'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', '123456', '123456'))->isSuccess());

        $property->setMaxLength(null)->setPattern('/^abc/');
        $this->assertTrue($validator->validate(new Parameter('test', 'abcdef', 'abcdef'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 'bcdef', 'bcdef'))->isSuccess());

        $property->setPattern(null)->setFormat(StringProperty::FORMAT_BYTE);
        $this->assertTrue($validator->validate(new Parameter('test', base64_encode('test'), base64_encode('test')))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 'not base64!', 'not base64!'))->isSuccess());
    }
}
