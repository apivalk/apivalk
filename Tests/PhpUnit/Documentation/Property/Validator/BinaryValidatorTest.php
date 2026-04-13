<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\BinaryValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class BinaryValidatorTest extends TestCase
{
    public function testValidBinary(): void
    {
        $property = new BinaryProperty('test');
        $validator = new BinaryValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 'any string value', 'any string value'))->isSuccess());
    }

    public function testNonStringValue(): void
    {
        $property = new BinaryProperty('test');
        $validator = new BinaryValidator($property);

        $result = $validator->validate(new Parameter('test', 123, 123));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_STRING, $result->getErrorKey());
    }

    public function testMinLength(): void
    {
        $property = new BinaryProperty('test');
        $property->setMinLength(5);
        $validator = new BinaryValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', '12345', '12345'))->isSuccess());

        $result = $validator->validate(new Parameter('test', '1234', '1234'));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_SHORTER_THAN_MIN_LENGTH, $result->getErrorKey());
    }

    public function testMaxLength(): void
    {
        $property = new BinaryProperty('test');
        $property->setMaxLength(5);
        $validator = new BinaryValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', '12345', '12345'))->isSuccess());

        $result = $validator->validate(new Parameter('test', '123456', '123456'));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_LONGER_THAN_MAX_LENGTH, $result->getErrorKey());
    }

    public function testPattern(): void
    {
        $property = new BinaryProperty('test');
        $property->setPattern('/^[a-z]+$/');
        $validator = new BinaryValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 'abcdef', 'abcdef'))->isSuccess());

        $result = $validator->validate(new Parameter('test', 'ABC', 'ABC'));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_DOES_NOT_MATCH_PATTERN, $result->getErrorKey());
    }
}
