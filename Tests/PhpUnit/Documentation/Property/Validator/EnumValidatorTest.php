<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\EnumValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class EnumValidatorTest extends TestCase
{
    public function testEnumValidator(): void
    {
        $property = new EnumProperty('test', '', ['a', 'b', 'c']);
        $validator = new EnumValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', 'a', 'a'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 'b', 'b'))->isSuccess());

        $result = $validator->validate(new Parameter('test', 'd', 'd'));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_VALID_ENUM_VALUE, $result->getErrorKey());
    }

    public function testNonStringValue(): void
    {
        $property = new EnumProperty('test', '', ['a', 'b']);
        $validator = new EnumValidator($property);

        $result = $validator->validate(new Parameter('test', 123, 123));
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_STRING, $result->getErrorKey());
    }
}
