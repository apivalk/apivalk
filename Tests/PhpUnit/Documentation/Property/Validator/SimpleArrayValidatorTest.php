<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\SimpleArrayProperty;
use apivalk\apivalk\Documentation\Property\Validator\SimpleArrayValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class SimpleArrayValidatorTest extends TestCase
{
    private function validate(string $itemType, $value): ValidatorResult
    {
        $property = new SimpleArrayProperty('test', '', $itemType);
        $validator = new SimpleArrayValidator($property);

        return $validator->validate(new Parameter('test', $value, $value));
    }

    public function testNonArrayValueIsRejected(): void
    {
        $result = $this->validate(SimpleArrayProperty::TYPE_INT, 123);
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_AN_ARRAY, $result->getErrorKey());
    }

    public function testJsonStringArrayIsBuiltAndValidated(): void
    {
        $this->assertTrue($this->validate(SimpleArrayProperty::TYPE_INT, '[1, 2, 3]')->isSuccess());
        $this->assertTrue($this->validate(SimpleArrayProperty::TYPE_STRING, '["a", "b"]')->isSuccess());
    }

    public function testNonJsonStringIsRejected(): void
    {
        $result = $this->validate(SimpleArrayProperty::TYPE_STRING, 'not a json array');
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_AN_ARRAY, $result->getErrorKey());
    }

    public function testEmptyArrayIsValid(): void
    {
        $this->assertTrue($this->validate(SimpleArrayProperty::TYPE_INT, [])->isSuccess());
    }

    public function testIntArrayWithValidValues(): void
    {
        $this->assertTrue($this->validate(SimpleArrayProperty::TYPE_INT, [1, 2, 3])->isSuccess());
        // numeric strings pass validation; ParameterBagFactory casts them to int when the value is fetched
        $this->assertTrue($this->validate(SimpleArrayProperty::TYPE_INT, ['1', '2'])->isSuccess());
    }

    public function testIntArrayWithInvalidElementIsRejected(): void
    {
        $result = $this->validate(SimpleArrayProperty::TYPE_INT, [1, 'not-a-number', 3]);
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_NUMERIC, $result->getErrorKey());
    }

    public function testStringArrayWithInvalidElementIsRejected(): void
    {
        $result = $this->validate(SimpleArrayProperty::TYPE_STRING, ['a', 5]);
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_A_STRING, $result->getErrorKey());
    }

    public function testNumberArrayWithValidValues(): void
    {
        $this->assertTrue($this->validate(SimpleArrayProperty::TYPE_NUMBER, [1.5, 2, '3.7'])->isSuccess());
    }

    public function testNumberArrayWithInvalidElementIsRejected(): void
    {
        $result = $this->validate(SimpleArrayProperty::TYPE_NUMBER, [1.5, 'abc']);
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_NUMERIC, $result->getErrorKey());
    }

    public function testBoolArrayWithValidValues(): void
    {
        $this->assertTrue($this->validate(SimpleArrayProperty::TYPE_BOOL, [true, false, 0, 1])->isSuccess());
    }

    public function testBoolArrayWithInvalidElementIsRejected(): void
    {
        $result = $this->validate(SimpleArrayProperty::TYPE_BOOL, [true, 'maybe']);
        $this->assertFalse($result->isSuccess());
        $this->assertSame(ValidatorResult::VALUE_IS_NOT_BOOLEAN, $result->getErrorKey());
    }
}
