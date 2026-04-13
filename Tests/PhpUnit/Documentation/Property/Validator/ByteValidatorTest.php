<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\ByteValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Property\ByteProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class ByteValidatorTest extends TestCase
{
    public function testValidBase64(): void
    {
        $property = new ByteProperty('test');
        $validator = new ByteValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', base64_encode('test'), base64_encode('test')))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', base64_encode('hello world'), base64_encode('hello world')))->isSuccess());
    }

    public function testInvalidBase64(): void
    {
        $property = new ByteProperty('test');
        $validator = new ByteValidator($property);

        $result = $validator->validate(new Parameter('test', 'not base64!', 'not base64!'));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_VALID_BASE64_STRING, $result->getErrorKey());
    }

    public function testNonStringValue(): void
    {
        $property = new ByteProperty('test');
        $validator = new ByteValidator($property);

        $result = $validator->validate(new Parameter('test', 123, 123));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_STRING, $result->getErrorKey());
    }

    public function testMinLength(): void
    {
        $property = new ByteProperty('test');
        $property->setMinLength(8);
        $validator = new ByteValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', base64_encode('hello'), base64_encode('hello')))->isSuccess());

        $result = $validator->validate(new Parameter('test', 'dGVz', 'dGVz')); // base64 of "tes" = 4 chars
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_SHORTER_THAN_MIN_LENGTH, $result->getErrorKey());
    }

    public function testMaxLength(): void
    {
        $property = new ByteProperty('test');
        $property->setMaxLength(4);
        $validator = new ByteValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 'dGVz', 'dGVz'))->isSuccess()); // 4 chars

        $result = $validator->validate(new Parameter('test', base64_encode('hello'), base64_encode('hello'))); // 8 chars
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_LONGER_THAN_MAX_LENGTH, $result->getErrorKey());
    }

    public function testPattern(): void
    {
        $property = new ByteProperty('test');
        $property->setPattern('/^[A-Z]/');
        $validator = new ByteValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 'SGVsbG8=', 'SGVsbG8='))->isSuccess()); // starts with uppercase

        $result = $validator->validate(new Parameter('test', 'dGVzdA==', 'dGVzdA==')); // starts with lowercase
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_DOES_NOT_MATCH_PATTERN, $result->getErrorKey());
    }
}
