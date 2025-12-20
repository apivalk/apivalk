<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\ArrayValidator;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;

class ArrayValidatorTest extends TestCase
{
    public function testArrayValidator()
    {
        $objProp = $this->createMock(AbstractObjectProperty::class);
        $property = new ArrayProperty('test', '', $objProp);
        $validator = new ArrayValidator($property);

        $this->assertTrue($validator->validate(['a', 'b'])->isSuccess());
        $this->assertTrue($validator->validate('["a", "b"]')->isSuccess());
        $this->assertFalse($validator->validate('not a json array')->isSuccess());
        $this->assertFalse($validator->validate(123)->isSuccess());
    }
}
