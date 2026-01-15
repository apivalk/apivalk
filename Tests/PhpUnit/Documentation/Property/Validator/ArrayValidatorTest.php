<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\ArrayValidator;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class ArrayValidatorTest extends TestCase
{
    public function testArrayValidator()
    {
        $objProp = $this->createMock(AbstractObjectProperty::class);
        $property = new ArrayProperty('test', '', $objProp);
        $validator = new ArrayValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', ['a', 'b'], ['a', 'b']))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', '["a", "b"]', '["a", "b"]'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 'not a json array', 'not a json array'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 123, 123))->isSuccess());
    }
}
